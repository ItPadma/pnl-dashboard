<?php

namespace App\Http\Controllers\PNL;

use App\Events\UserEvent;
use App\Exports\PajakKeluaranDetailExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilities\LogController;
use App\Imports\PajakMasukanCoretaxImport;
use App\Models\MasterDepo;
use App\Models\MasterPkp;
use App\Models\PajakKeluaranDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AccessGroup;
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
            $dbquery = DB::table('pajak_keluaran_details')->select('*');
            $filters = $this->applyFilters($dbquery, $request);

            // Retrieve from live if no records found
            while ($retrieve_count == 0 && $dbquery->count() == 0) {
                Log::info('No records found in database, fetching from live');
                broadcast(new UserEvent('info', 'Info', 'Record tidak ditemukan di database, mengambil data dari live...', Auth::user()->id));
                PajakKeluaranDetail::getFromLive($request->pt, $request->brand, $request->depo, $filters['periode_awal'], $filters['periode_akhir'], $filters['tipe'], $filters['chstatus']);
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
                'data' => [],
            ]);
        }
    }

    public function updateMove2(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required',
                'move_from' => 'required|in:pkp,pkpnppn,npkp,npkpnppn,retur',
                'move_to' => 'required|in:pkp,pkpnppn,npkp,npkpnppn,retur',
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
                'data' => $item,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => [],
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
            if ($request->has('select_all') && $request->select_all == 1) {
                // Determine scope: 'all' or 'ids'
                // If ids are sent, it might be visible only, but if select_all is flagged, 
                // we probably want to filter by query query params which should be passed.
                // However, the standard DataTables params must be present.
                
                $dbquery = DB::table('pajak_keluaran_details');
                $this->applyFilters($dbquery, $request);
                $dbquery->update(['is_checked' => $isChecked]);

            } elseif ($request->has('ids')) {
                $ids = $request->input('ids');
                PajakKeluaranDetail::whereIn('id', $ids)->update(['is_checked' => $isChecked]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Status updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function count(Request $request)
    {
        try {
            $tipe = $request->query('tipe') ?? 'all';
            $pt = $request->pt ?? ['all'];
            $brand = $request->brand ?? ['all'];
            $depo = $request->depo ?? ['all'];
            
            // Ensure inputs are arrays
            if (!is_array($pt)) $pt = [$pt];
            if (!is_array($brand)) $brand = [$brand];
            if (!is_array($depo)) $depo = [$depo];
            $periode_awal = null;
            $periode_akhir = null;
            if ($request->has('periode') && !empty($request->periode)) {
                $periode_parts = explode(' - ', $request->periode);
                if (count($periode_parts) === 2) {
                    $periode_awal = \Carbon\Carbon::createFromFormat('d/m/Y', $periode_parts[0])->format('Y-m-d');
                    $periode_akhir = \Carbon\Carbon::createFromFormat('d/m/Y', $periode_parts[1])->format('Y-m-d');
                }
            }

            // Base query
            $query = PajakKeluaranDetail::query();
            $query->selectRaw('
                ISNULL(SUM(CASE WHEN is_downloaded = 0 AND is_checked = 1 THEN 1 ELSE 0 END), 0) AS ready2download_count,
                ISNULL(SUM(CASE WHEN is_downloaded = 1 AND is_checked = 1 THEN 1 ELSE 0 END), 0) AS downloaded_count
            ');

            // Additional filters
            if (!in_array('all', $pt)) {
                $query->whereIn('company', $pt);
            }
            if (!in_array('all', $brand)) {
               $query->whereIn('brand', $brand);
            }
            if (!in_array('all', $depo)) {
                $depoNames = MasterDepo::whereIn('code', $depo)->get()->pluck('name')->toArray();
                $query->whereIn('depo', $depoNames);
            }
            if ($periode_awal && $periode_akhir) {
                $query->where('tgl_faktur_pajak', '>=', $periode_awal)
                    ->where('tgl_faktur_pajak', '<=', $periode_akhir);
            }
            if ($request->has('chstatus')) {
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
                        tipe_ppn = 'PPN' AND (hargatotal_sblm_ppn > 0 OR hargatotal_sblm_ppn <= -1000000) AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp)
                    ) OR (has_moved = 'y' AND moved_to = 'npkp')");
                    break;
                case 'npkpnppn':
                    $query->whereRaw("
                    (
                        tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp)
                    ) OR (has_moved = 'y' AND moved_to = 'npkpnppn')");
                    break;
                case 'retur':
                    $query->whereRaw("qty_pcs < 0 AND hargatotal_sblm_ppn >= -1000000 AND has_moved = 'n' OR moved_to = 'retur'");
                    break;
            }
            // Log::info('sql count: '.$query->toSql());
            $counts = $query->get();

            return response()->json([
                'status' => true,
                'message' => 'Counts retrieved successfully',
                'data' => $counts,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function download(Request $request)
    {
        try {
            if (!Auth::user()->canAccessMenu('reguler-pajak-keluaran', AccessGroup::LEVEL_READ_WRITE)) {
                abort(403, 'Unauthorized action.');
            }
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
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function getAvailableDates(Request $request)
    {
        try {
            // Build query to get distinct dates
            $query = DB::table('pajak_keluaran_details')
                ->select(DB::raw('DISTINCT CAST(tgl_faktur_pajak AS DATE) as tanggal'))
                ->whereNotNull('tgl_faktur_pajak');

            // Apply filters
            if ($request->has('pt') && ! in_array('all', $request->pt)) {
                $query->whereRaw("company IN ('".implode("','", $request->pt)."')");
            }

            if ($request->has('brand') && ! in_array('all', $request->brand)) {
                $query->whereRaw("brand IN ('".implode("','", $request->brand)."')");
            }

            if ($request->has('depo') && ! in_array('all', $request->depo)) {
                $depos = MasterDepo::whereIn('code', $request->depo)->get()->pluck('name')->toArray();
                if (!empty($depos)) {
                     $query->whereIn('depo', $depos);
                }
            }

            // Get dates and format them
            $dates = $query->orderBy('tanggal', 'asc')->get();
            $formattedDates = $dates->map(function ($item) {
                return \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d');
            })->toArray();

            return response()->json([
                'status' => true,
                'data' => $formattedDates,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function uploadPMCoretax(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt,xlsx,xls',
            ]);

            $file = $request->file('file');
            $path = $file->store('public/import');

            // Create import instance
            $import = new PajakMasukanCoretaxImport;

            // Execute import
            Excel::import($import, $path);

            // Get import statistics
            $insertedCount = $import->getInsertedCount();
            $updatedCount = $import->getUpdatedCount();
            $errorCount = $import->getErrorCount();
            $errorMessages = $import->getErrorMessages();
            $totalProcessed = $insertedCount + $updatedCount;

            // Create log with statistics
            LogController::createLog(
                $request->user()->id,
                'Import Pajak Masukan Coretax',
                "Import Pajak Masukan Coretax - Inserted: {$insertedCount}, Updated: {$updatedCount}, Errors: {$errorCount}",
                '-',
                'pajak_masukan_coretax',
                'info',
                $request
            );

            // Create success message with details
            $message = 'Data berhasil diimport! ';
            $message .= "Berhasil insert: {$insertedCount}, ";
            $message .= "Duplikat (diupdate): {$updatedCount}, ";
            $message .= "Error: {$errorCount}, ";
            $message .= "Total diproses: {$totalProcessed}";

            $responseData = [
                'success' => $message,
                'stats' => [
                    'inserted' => $insertedCount,
                    'updated' => $updatedCount,
                    'errors' => $errorCount,
                    'total' => $totalProcessed,
                ],
            ];

            if ($errorCount > 0) {
                $responseData['error_messages'] = $errorMessages;
            }

            return redirect()->back()->with($responseData);

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Import gagal: '.$th->getMessage());
        }
    }

    private function applyFilters($dbquery, Request $request)
    {
        $metadata = [
            'periode_awal' => null,
            'periode_akhir' => null,
            'tipe' => '',
            'chstatus' => ''
        ];

        $searchValue = $request->get('search')['value'] ?? '';

        // Column specific filters
        if ($request->has('columns')) {
            foreach ($request->get('columns') as $column) {
                if (! empty($column['search']['value'])) {
                    $dbquery->where($column['data'], 'like', "%{$column['search']['value']}%");
                }
            }
        }

        // Filtering by search value
        if (! empty($searchValue)) {
            $dbquery->where(function ($q) use ($searchValue) {
                $q->where('no_invoice', 'like', "%{$searchValue}%")
                    ->orWhere('no_do', 'like', "%{$searchValue}%")
                    ->orWhere('kode_produk', 'like', "%{$searchValue}%")
                    ->orWhere('nama_produk', 'like', "%{$searchValue}%")
                    ->orWhere('brand', 'like', "%{$searchValue}%")
                    ->orWhere('depo', 'like', "%{$searchValue}%")
                    ->orWhere('customer_id', 'like', "%{$searchValue}%")
                    ->orWhere('nik', 'like', "%{$searchValue}%")
                    ->orWhere('nama_customer_sistem', 'like', "%{$searchValue}%");
            });
        }
        // Additional filters
        if ($request->has('pt') && ! in_array('all', $request->pt ?? [])) {
            $dbquery->whereRaw("company IN ('".implode("','", $request->pt)."')");
        }
        if ($request->has('brand') && ! in_array('all', $request->brand ?? [])) {
            $dbquery->whereRaw("brand IN ('".implode("','", $request->brand)."')");
        }
        if ($request->has('depo') && ! in_array('all', $request->depo ?? [])) {
            $userInfo = getLoggedInUserInfo();
            
            // If user has specific depo access, intersect requested depos with allowed depos
            if ($userInfo && !in_array('all', $userInfo->depo)) {
                // Filter requested depos that user actually has access to
                $allowedDepos = MasterDepo::whereIn('code', $userInfo->depo)->get()->pluck('name')->toArray();
                
                // Get names of requested depos
                $requestedDepos = MasterDepo::whereIn('code', $request->depo)->get()->pluck('name')->toArray();
                
                // Intersect to ensure user only accesses allowed depos
                $validDepos = array_intersect($requestedDepos, $allowedDepos);
                
                if (!empty($validDepos)) {
                     $dbquery->whereIn('depo', $validDepos);
                } else {
                    // If intersection is empty (user requesting access to unauthorized depos), return no results
                     $dbquery->whereRaw("1 = 0");
                }
            } else {
                // User has 'all' access, so just use requested depos
                 $depos = MasterDepo::whereIn('code', $request->depo)->get()->pluck('name')->toArray();
                 $dbquery->whereIn('depo', $depos);
            }
        } elseif ($request->has('depo') && in_array('all', $request->depo ?? [])) {
             // Logic for 'all' selection
             $userInfo = getLoggedInUserInfo();
             if ($userInfo && !in_array('all', $userInfo->depo)) {
                $depo = MasterDepo::whereIn('code', $userInfo->depo)->get()->pluck('name')->toArray();
                $dbquery->whereIn('depo', $depo);
             }
        }
        if ($request->has('periode') && ! empty($request->periode)) {
            $periode = explode(' - ', $request->periode);
            if (count($periode) === 2) {
                $metadata['periode_awal'] = \Carbon\Carbon::createFromFormat('d/m/Y', $periode[0])->format('Y-m-d');
                $metadata['periode_akhir'] = \Carbon\Carbon::createFromFormat('d/m/Y', $periode[1])->format('Y-m-d');
                $dbquery->whereRaw("tgl_faktur_pajak >= '{$metadata['periode_awal']}' AND tgl_faktur_pajak <= '{$metadata['periode_akhir']}'");
            }
        }
        if ($request->has('chstatus')) {
            switch ($request->chstatus) {
                case 'checked-ready2download':
                    $dbquery->where('is_checked', 1);
                    $metadata['chstatus'] = ' AND is_checked = 1';
                    break;

                case 'unchecked':
                    $dbquery->where('is_checked', 0);
                    $metadata['chstatus'] = ' AND is_checked = 0';
                    break;

                case 'checked-downloaded':
                    $dbquery->where('is_checked', 1);
                    $dbquery->where('is_downloaded', 1);
                    $metadata['chstatus'] = ' AND is_checked = 1 AND is_downloaded = 1';
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
                $metadata['tipe'] = " AND e.szTaxTypeId = 'PPN' AND a.szCustId IN ('".implode("','", $pkp)."')";
            }
            if ($request->tipe == 'pkpnppn') {
                $dbquery->whereRaw("
                (
                    tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id IN (SELECT IDPelanggan FROM master_pkp)
                ) OR (has_moved = 'y' AND moved_to = 'pkpnppn')");
                $metadata['tipe'] = " AND e.szTaxTypeId = 'NON-PPN' AND a.szCustId IN ('".implode("','", $pkp)."')";
            }
            if ($request->tipe == 'npkp') {
                $dbquery->whereRaw("
                (
                    tipe_ppn = 'PPN' AND (hargatotal_sblm_ppn > 0 OR hargatotal_sblm_ppn <= -1000000) AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp)
                ) OR (has_moved = 'y' AND moved_to = 'npkp')");
                $metadata['tipe'] = " AND e.szTaxTypeId = 'PPN' AND a.szCustId NOT IN ('".implode("','", $pkp)."')";
            }
            if ($request->tipe == 'npkpnppn') {
                $dbquery->whereRaw("
                (
                    tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp)
                ) OR (has_moved = 'y' AND moved_to = 'npkpnppn')");
                $metadata['tipe'] = " AND e.szTaxTypeId = 'NON-PPN' AND a.szCustId NOT IN ('".implode("','", $pkp)."')";
            }
            if ($request->tipe == 'retur') {
                $dbquery->whereRaw("qty_pcs < 0 AND hargatotal_sblm_ppn >= -1000000 AND has_moved = 'n' OR moved_to = 'retur'");
            }
        }
        
        return $metadata;
    }
}
