<?php

namespace App\Http\Controllers\PNL;

use App\Exports\PajakKeluaranDetailExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilities\LogController;
use App\Models\MasterDepo;
use App\Models\MasterPkp;
use App\Models\PajakKeluaranDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class RegulerController extends Controller
{
    public function pkIndex()
    {
        return view('pnl.reguler.pajak-keluaran.index');
    }

    public function pmIndex()
    {
        return view('pnl.reguler.pajak-masukan.index');
    }

    public function dtPKGetData(Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $rowperpage = $request->get('length');
            $columnIndex = $request->get('order')[0]['column'] ?? 0;
            $columnName = $request->get('columns')[$columnIndex]['data'] ?? 'created_at';
            $columnSortOrder = $request->get('order')[0]['dir'] ?? 'desc';
            $searchValue = $request->get('search')['value'] ?? '';
            $tipe = '';
            $chstatus = '';
            $retrieve_count = 0;
            // Query base
            $query = PajakKeluaranDetail::query();
            // Filtering by search value
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('no_invoice', 'like', "%{$searchValue}%")
                      ->orWhere('no_do', 'like', "%{$searchValue}%")
                      ->orWhere('kode_produk', 'like', "%{$searchValue}%")
                      ->orWhere('nama_produk', 'like', "%{$searchValue}%")
                      ->orWhere('brand', 'like', "%{$searchValue}%")
                      ->orWhere('depo', 'like', "%{$searchValue}%");
                });
            }
            // Column specific filters
            foreach ($request->get('columns') as $column) {
                if (!empty($column['search']['value'])) {
                    $query->where($column['data'], 'like', "%{$column['search']['value']}%");
                }
            }
            // Additional filters
            if ($request->has('pt') && $request->pt != 'all') {
                $query->where('company', $request->pt);
            }
            if ($request->has('brand') && $request->brand != 'all') {
                $query->where('brand', $request->brand);
            }
            if ($request->has('depo') && $request->depo == 'all') {
                $currentUserDepo = Auth::user()->depo;
                if (str_contains($currentUserDepo, '|')) {
                    $currentUserDepo = explode("|", $currentUserDepo);
                    if (!in_array('all', $currentUserDepo)) {
                        $depo = MasterDepo::whereIn('code', $currentUserDepo)->get()->pluck('name')->toArray();
                        $query->whereIn('depo', $depo);
                    }
                }
            }
            if ($request->has('depo') && $request->depo != 'all') {
                $depo = MasterDepo::where('code', $request->depo)->first();
                $query->where('depo', $depo->name);
            }
            if ($request->has('periode')) {
                $periode = explode(' - ', $request->periode);
                $periode_awal = \Carbon\Carbon::createFromFormat('d/m/Y', $periode[0])->format('Y-m-d');
                $periode_akhir = \Carbon\Carbon::createFromFormat('d/m/Y', $periode[1])->format('Y-m-d');
                $query->where('tgl_faktur_pajak', '>=', $periode_awal)
                      ->where('tgl_faktur_pajak', '<=', $periode_akhir);
            }
            if ($request->has('tipe')) {
                $pkp = MasterPkp::all()->pluck('IDPelanggan')->toArray();
                if ($request->tipe == 'pkp') {
                    $query->whereIn('customer_id', $pkp);
                    $query->where('tipe_ppn', 'PPN');
                    $query->orWhere('moved_to', 'pkp');
                    $tipe = " AND e.szTaxTypeId = 'PPN' AND a.szCustId IN ('" . implode("','", $pkp) . "')";
                }
                if ($request->tipe == 'pkpnppn') {
                    $query->whereIn('customer_id', $pkp);
                    $query->where('tipe_ppn', 'NON-PPN');
                    $query->orWhere('moved_to', 'pkpnppn');
                    $tipe = " AND e.szTaxTypeId = 'NON-PPN' AND a.szCustId IN ('" . implode("','", $pkp) . "')";
                }
                if ($request->tipe == 'npkp') {
                    $query->whereNotIn('customer_id', $pkp);
                    $query->where('tipe_ppn', 'PPN');
                    $query->orWhere('moved_to', 'npkp');
                    $tipe = " AND e.szTaxTypeId = 'PPN' AND a.szCustId NOT IN ('" . implode("','", $pkp) . "')";
                }
                if ($request->tipe == 'npkpnppn') {
                    $query->whereNotIn('customer_id', $pkp);
                    $query->where('tipe_ppn', 'NON-PPN');
                    $query->orWhere('moved_to', 'npkpnppn');
                    $tipe = " AND e.szTaxTypeId = 'NON-PPN' AND a.szCustId NOT IN ('" . implode("','", $pkp) . "')";
                }
                if ($request->tipe == 'retur') {
                    $query->where('qty_pcs', '<', 0);
                    $query->orWhere('moved_to', 'retur');
                    $tipe = " AND b.decUomQty < 0";
                }
            }
            if($request->has('chstatus')) {
                switch ($request->chstatus) {
                    case 'checked-ready2download':
                        $query->where('is_checked', 1);
                        $chstatus = ' AND is_checked = 1';
                        break;

                    case 'unchecked':
                        $query->where('is_checked', 0);
                        $chstatus = ' AND is_checked = 0';
                        break;

                    case 'checked-downloaded':
                        $query->where('is_checked', 1);
                        $query->where('is_downloaded', 1);
                        $chstatus = ' AND is_checked = 1 AND is_downloaded = 1';
                        break;

                    default:
                        break;
                }
            }
            while ($retrieve_count == 0 && $query->count() == 0) {
                Log::info('No records found in database, fetching from live');
                PajakKeluaranDetail::getFromLive($request->pt, $request->brand, $request->depo, $periode_awal, $periode_akhir, $tipe, $chstatus);
                $retrieve_count = 1;
            }
            // Total records
            $totalRecords = $query->count();
            $totalRecordswithFilter = $query->count();
            $records = $query->orderBy($columnName, $columnSortOrder)
                ->skip($start)
                ->take($rowperpage)
                ->get();
            return response()->json([
                'draw' => intval($draw),
                'iTotalRecords' => $totalRecords,
                'iTotalDisplayRecords' => $totalRecordswithFilter,
                'aaData' => $records,
                'status' => true,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => []
            ]);
        }
    }

    public function updateMove2(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'move_from' => 'required|in:pkp,pkpnppn,npkp,npkpnppn,retur',
                'move_to' => 'required|in:pkp,pkpnppn,npkp,npkpnppn,retur'
            ]);
            $id = $request->input('id');
            $item = PajakKeluaranDetail::findOrFail($id);
            $item->has_moved = 'y';
            $item->moved_to = $request->input('move_to');
            $item->moved_at = now();
            $item->save();

            LogController::createLog(
                Auth::user()->id,
                'Move Item from '.$request->input('move_from').' to '.$request->input('move_to'),
                'Update',
                '{id: '.$id.', no_invoice: '.$item->no_invoice.', no_do: '.$item->no_do.', kode_produk: '.$item->kode_produk.', move_from: '.$request->input('move_from').', move_to: '.$request->input('move_to').'}',
                'pajak_keluaran_details',
                'info',
                $request
            );

            return response()->json([
                'status' => true,
                'message' => 'Item moved successfully',
                'data' => $item
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => []
            ]);
        }
    }

    public function updateChecked(Request $request)
    {
        try {
            if ($request->has('id')) {
                $id = $request->input('id');
                $isChecked = $request->input('is_checked');

                $item = PajakKeluaranDetail::findOrFail($id);
                $item->is_checked = $isChecked;
                $item->save();
            }
            if ($request->has('ids')) {
                $ids = $request->input('ids');
                $isChecked = $request->input('is_checked');

                PajakKeluaranDetail::whereIn('id', $ids)->update(['is_checked' => $isChecked]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function count(Request $request)
    {
        try {
            $tipe = $request->query('tipe') ?? 'all';
            $pt = $request->pt ?? 'all';
            $brand = $request->brand ?? 'all';
            $depo = $request->depo ?? 'all';
            $periode = $request->periode ?? null;
            $periode = explode(' - ', $periode);
            $periode_awal = \Carbon\Carbon::createFromFormat('d/m/Y', $periode[0])->format('Y-m-d');
            $periode_akhir = \Carbon\Carbon::createFromFormat('d/m/Y', $periode[1])->format('Y-m-d');

            // Base query
            $query = PajakKeluaranDetail::query();

            // Additional filters
            if ($pt != 'all') {
                $query->where('company', $pt);
            }
            if ($brand != 'all') {
                $query->where('brand', $brand);
            }
            if ($depo != 'all') {
                $query->where('depo', $depo);
            }
            if ($periode_awal && $periode_akhir) {
                $query->whereBetween('tgl_faktur_pajak', [$periode_awal, $periode_akhir]);
            }
            if($request->has('chstatus')) {
                switch ($request->chstatus) {
                    case 'checked-ready2download':
                        $query->where('is_checked', 1);
                        break;

                    case 'unchecked':
                        $query->where('is_checked', 0);
                        break;

                    case 'checked-downloaded':
                        $query->where('is_downloaded', 1);
                        break;

                    default:
                        break;
                }
            }

            // Additional conditions based on the type
            $pkp = MasterPkp::all()->pluck('IDPelanggan')->toArray();
            switch ($tipe) {
                case 'pkp':
                    $query->whereIn('customer_id', $pkp);
                    $query->where('tipe_ppn', 'PPN');
                    break;
                case 'pkpnppn':
                    $query->whereIn('customer_id', $pkp);
                    $query->where('tipe_ppn', 'NON-PPN');
                    break;
                case 'npkp':
                    $query->whereNotIn('customer_id', $pkp);
                    $query->where('tipe_ppn', 'PPN');
                    break;
                case 'npkpnppn':
                    $query->whereNotIn('customer_id', $pkp);
                    $query->where('tipe_ppn', 'NON-PPN');
                    break;
                case 'retur':
                    $query->where('qty_pcs', '<', 0);
                    break;
            }

            // Add the custom raw SQL to the query
            $counts = $query->selectRaw('
                SUM(CASE WHEN is_downloaded = 0 THEN 1 ELSE 0 END) AS ready2download_count,
                SUM(CASE WHEN is_downloaded = 1 THEN 1 ELSE 0 END) AS downloaded_count
            ')
            ->where('is_checked', 1)
            ->get();

            // Assuming you want to return the counts as JSON
            return response()->json([
                'status' => true,
                'message' => 'Counts retrieved successfully',
                'data' => $counts
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function download(Request $request)
    {
        try {
            $tipe = $request->query('tipe');
            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="pajak_keluaran_'.$tipe.'.xlsx"',
            ];
            $writerType = 'Xlsx';
            return Excel::download(new PajakKeluaranDetailExport($tipe), 'pajak_keluaran_'.$tipe.'.xlsx', $writerType, $headers);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
