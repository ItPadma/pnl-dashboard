<?php

namespace App\Http\Controllers\PNL;

use App\Exports\PajakKeluaranDetailExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilities\LogController;
use App\Imports\PajakMasukanCoretaxImport;
use App\Models\MasterDepo;
use App\Models\MasterPkp;
use App\Models\PajakKeluaranDetail;
use App\Models\PajakMasukanCoretax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function pmUploadCsvIndex()
    {
        return view('pnl.reguler.pajak-masukan.upload');
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
            $dbquery = DB::table('pajak_keluaran_details')
                ->select('*');
            // Filtering by search value
            if (!empty($searchValue)) {
                $dbquery->where(function($q) use ($searchValue) {
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
                    $dbquery->where($column['data'], 'like', "%{$column['search']['value']}%");
                }
            }
            // Additional filters
            if ($request->has('pt') && $request->pt != 'all') {
                $dbquery->whereRaw("company = '$request->pt'");
            }
            if ($request->has('brand') && $request->brand != 'all') {
                $dbquery->whereRaw("brand = '$request->brand'");
            }
            if ($request->has('depo') && $request->depo == 'all') {
                $currentUserDepo = Auth::user()->depo;
                if (str_contains($currentUserDepo, '|')) {
                    $currentUserDepo = explode("|", $currentUserDepo);
                    if (!in_array('all', $currentUserDepo)) {
                        $depo = MasterDepo::whereIn('code', $currentUserDepo)->get()->pluck('name')->toArray();
                        $dbquery->whereIn('depo', $depo);
                    }
                }
            }
            if ($request->has('depo') && $request->depo != 'all') {
                $depo = MasterDepo::where('code', $request->depo)->first();
                $dbquery->whereRaw("depo = '$depo->name'");
            }
            if ($request->has('periode')) {
                $periode = explode(' - ', $request->periode);
                $periode_awal = \Carbon\Carbon::createFromFormat('d/m/Y', $periode[0])->format('Y-m-d');
                $periode_akhir = \Carbon\Carbon::createFromFormat('d/m/Y', $periode[1])->format('Y-m-d');
                $dbquery->whereRaw("tgl_faktur_pajak >= '$periode_awal' AND tgl_faktur_pajak <= '$periode_akhir'");
            }
            if($request->has('chstatus')) {
                switch ($request->chstatus) {
                    case 'checked-ready2download':
                        $dbquery->where('is_checked', 1);
                        $chstatus = ' AND is_checked = 1';
                        break;

                    case 'unchecked':
                        $dbquery->where('is_checked', 0);
                        $chstatus = ' AND is_checked = 0';
                        break;

                    case 'checked-downloaded':
                        $dbquery->where('is_checked', 1);
                        $dbquery->where('is_downloaded', 1);
                        $chstatus = ' AND is_checked = 1 AND is_downloaded = 1';
                        break;

                    default:
                        break;
                }
            }
            if ($request->has('tipe')) {
                $pkp = MasterPkp::all()->pluck('IDPelanggan')->toArray();
                if ($request->tipe == 'pkp') {
                    $dbquery->whereRaw("
                    (
                        tipe_ppn = 'PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id IN (SELECT IDPelanggan FROM master_pkp)
                    )
                    OR
                    (has_moved = 'y' AND moved_to = 'pkp')");
                    $tipe = " AND e.szTaxTypeId = 'PPN' AND a.szCustId IN ('" . implode("','", $pkp) . "')";
                }
                if ($request->tipe == 'pkpnppn') {
                    $dbquery->whereRaw("
                    (
                        tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id IN (SELECT IDPelanggan FROM master_pkp)
                    ) OR (has_moved = 'y' AND moved_to = 'pkpnppn')");
                    $tipe = " AND e.szTaxTypeId = 'NON-PPN' AND a.szCustId IN ('" . implode("','", $pkp) . "')";
                }
                if ($request->tipe == 'npkp') {
                    $dbquery->whereRaw("
                    (
                        tipe_ppn = 'PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp)
                    ) OR (has_moved = 'y' AND moved_to = 'npkp')");
                    $tipe = " AND e.szTaxTypeId = 'PPN' AND a.szCustId NOT IN ('" . implode("','", $pkp) . "')";
                }
                if ($request->tipe == 'npkpnppn') {
                    $dbquery->whereRaw("
                    (
                        tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp)
                    ) OR (has_moved = 'y' AND moved_to = 'npkpnppn')");
                    $tipe = " AND e.szTaxTypeId = 'NON-PPN' AND a.szCustId NOT IN ('" . implode("','", $pkp) . "')";
                }
                if ($request->tipe == 'retur') {
                    $dbquery->whereRaw("qty_pcs < 0 AND has_moved = 'n' OR moved_to = 'retur'");
                }
            }
            // Retrieve from live if no records found
            while ($retrieve_count == 0 && $dbquery->count() == 0) {
                Log::info('No records found in database, fetching from live');
                PajakKeluaranDetail::getFromLive($request->pt, $request->brand, $request->depo, $periode_awal, $periode_akhir, $tipe, $chstatus);
                $retrieve_count = 1;
            }
            // Total records
            $totalRecords = $dbquery->count();
            $totalRecordswithFilter = $dbquery->count();
            $records = $dbquery->orderBy($columnName)
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
                'ids' => 'required',
                'move_from' => 'required|in:pkp,pkpnppn,npkp,npkpnppn,retur',
                'move_to' => 'required|in:pkp,pkpnppn,npkp,npkpnppn,retur'
            ]);
            $ids = $request->input('ids');
            $items = PajakKeluaranDetail::whereIn('id', $ids)->get();
            foreach ($items as $item) {
                $item->has_moved = 'y';
                $item->moved_to = $request->input('move_to');
                $item->moved_at = now();
                $item->save();
                LogController::createLog(
                    Auth::user()->id,
                    'Move Item from '.$request->input('move_from').' to '.$request->input('move_to'),
                    'Update',
                    '{id: '.$item->id.', no_invoice: '.$item->no_invoice.', no_do: '.$item->no_do.', kode_produk: '.$item->kode_produk.', move_from: '.$request->input('move_from').', move_to: '.$request->input('move_to').'}',
                    'pajak_keluaran_details',
                    'info',
                    $request
                );
            }


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
            $query->selectRaw('
                ISNULL(SUM(CASE WHEN is_downloaded = 0 AND is_checked = 1 THEN 1 ELSE 0 END), 0) AS ready2download_count,
                ISNULL(SUM(CASE WHEN is_downloaded = 1 AND is_checked = 1 THEN 1 ELSE 0 END), 0) AS downloaded_count
            ');

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
                $query->where('tgl_faktur_pajak', '>=', $periode_awal)
                      ->where('tgl_faktur_pajak', '<=', $periode_akhir);
            }
            if($request->has('chstatus')) {
                switch ($request->chstatus) {
                    case 'checked-ready2download':
                        $query->where('is_checked', '1');
                        break;

                    case 'unchecked':
                        $query->where('is_checked', '0');
                        break;

                    case 'checked-downloaded':
                        $query->where('is_downloaded', '1');
                        break;

                    default:
                        break;
                }
            }

            // Additional conditions based on the type
            $pkp = MasterPkp::all()->pluck('IDPelanggan')->toArray();
            switch ($tipe) {
                case 'pkp':
                    $query->whereRaw("
                    (
                        tipe_ppn = 'PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id IN (SELECT IDPelanggan FROM master_pkp)
                    )
                    OR
                    (has_moved = 'y' AND moved_to = 'pkp')");
                    break;
                case 'pkpnppn':
                    $query->whereRaw("
                    (
                        tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id IN (SELECT IDPelanggan FROM master_pkp)
                    ) OR (has_moved = 'y' AND moved_to = 'pkpnppn')");
                    break;
                case 'npkp':
                    $query->whereRaw("
                    (
                        tipe_ppn = 'PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp)
                    ) OR (has_moved = 'y' AND moved_to = 'npkp')");
                    break;
                case 'npkpnppn':
                    $query->whereRaw("
                    (
                        tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp)
                    ) OR (has_moved = 'y' AND moved_to = 'npkpnppn')");
                    break;
                case 'retur':
                    $query->whereRaw("qty_pcs < 0 AND has_moved = 'n' OR moved_to = 'retur'");
                    break;
            }
            // Log::info('sql count: '.$query->toSql());
            $counts = $query->get();
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

    public function uploadPMCoretax(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt,xlsx,xls'
            ]);
            $file = $request->file('file');
            $path = $file->store('public/import');
            Excel::import(new PajakMasukanCoretaxImport, $path);
            LogController::createLog($request->user()->id, 'Import Pajak Masukan Coretax', 'Import Pajak Masukan Coretax', '-', 'pajak_masukan_coretax', 'info', $request);
            return redirect()->back()->with('success', 'Data imported successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
