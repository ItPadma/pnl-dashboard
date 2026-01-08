<?php

namespace App\Http\Controllers\PNL;

use App\Events\UserEvent;
use App\Exports\NettInvoiceExport;
use App\Http\Controllers\Controller;
use App\Models\MasterDepo;
use App\Models\MasterPkp;
use App\Models\NettInvoiceDetail;
use App\Models\NettInvoiceDetailItem;
use App\Models\NettInvoiceHeader;
use App\Models\NettInvoiceHeaderItem;
use App\Models\PajakKeluaranDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class NettInvoiceController extends Controller
{
    public function index()
    {
        return view('pnl.reguler.pajak-keluaran.nett-invoice.index');
    }

    /**
     * Get data for DataTables with filters
     */
    public function getData(Request $request)
    {
        try {
            $query = DB::table('pajak_keluaran_details')
                ->select(
                    'customer_id as kode_pelanggan',
                    'nama_customer_sistem as nama_pelanggan',
                    'no_invoice',
                    DB::raw('SUM(dpp + ppn) as nilai_invoice'),
                    DB::raw('0 as nett_invoice')
                )
                ->where('is_checked', 1)
                ->where('is_downloaded', 0);

            // Apply filters
            if ($request->has('pt') && !in_array('all', $request->pt ?? [])) {
                $query->whereIn('company', $request->pt);
            }

            if ($request->has('brand') && !in_array('all', $request->brand ?? [])) {
                $query->whereIn('brand', $request->brand);
            }

            if ($request->has('depo') && !in_array('all', $request->depo ?? [])) {
                $userInfo = getLoggedInUserInfo();
                
                if ($userInfo && !in_array('all', $userInfo->depo)) {
                    $allowedDepos = \App\Models\MasterDepo::whereIn('code', $userInfo->depo)
                        ->get()->pluck('name')->toArray();
                    $requestedDepos = \App\Models\MasterDepo::whereIn('code', $request->depo)
                        ->get()->pluck('name')->toArray();
                    $validDepos = array_intersect($requestedDepos, $allowedDepos);
                    
                    if (!empty($validDepos)) {
                        $query->whereIn('depo', $validDepos);
                    } else {
                        $query->whereRaw("1 = 0");
                    }
                } else {
                    $depos = \App\Models\MasterDepo::whereIn('code', $request->depo)
                        ->get()->pluck('name')->toArray();
                    $query->whereIn('depo', $depos);
                }
            } elseif ($request->has('depo') && in_array('all', $request->depo ?? [])) {
                $userInfo = getLoggedInUserInfo();
                if ($userInfo && !in_array('all', $userInfo->depo)) {
                    $depo = \App\Models\MasterDepo::whereIn('code', $userInfo->depo)
                        ->get()->pluck('name')->toArray();
                    $query->whereIn('depo', $depo);
                }
            }

            if ($request->has('periode') && !empty($request->periode)) {
                $periode = explode(' - ', $request->periode);
                if (count($periode) === 2) {
                    $periodeAwal = \Carbon\Carbon::createFromFormat('d/m/Y', $periode[0])->format('Y-m-d');
                    $periodeAkhir = \Carbon\Carbon::createFromFormat('d/m/Y', $periode[1])->format('Y-m-d');
                    $query->whereRaw("tgl_faktur_pajak >= '{$periodeAwal}' AND tgl_faktur_pajak <= '{$periodeAkhir}'");
                }
            }

            // Apply TIPE filter
            if ($request->has('tipe') && $request->tipe !== 'all') {
                $pkp = MasterPkp::all()->pluck('IDPelanggan')->toArray();
                
                switch ($request->tipe) {
                    case 'pkp':
                        $query->whereIn('customer_id', $pkp);
                        $query->where('tipe_ppn', 'PPN');
                        $query->where('qty_pcs', '>', 0);
                        break;
                    case 'pkpnppn':
                        $query->whereIn('customer_id', $pkp);
                        $query->where('tipe_ppn', 'NON-PPN');
                        $query->where('qty_pcs', '>', 0);
                        break;
                    case 'npkp':
                        $query->whereNotIn('customer_id', $pkp);
                        $query->where('tipe_ppn', 'PPN');
                        $query->where('qty_pcs', '>', 0);
                        break;
                    case 'npkpnppn':
                        $query->whereNotIn('customer_id', $pkp);
                        $query->where('tipe_ppn', 'NON-PPN');
                        $query->where('qty_pcs', '>', 0);
                        break;
                    case 'retur':
                        $query->where('qty_pcs', '<', 0);
                        break;
                }
            } else {
                // Default: exclude retur if 'all'
                $query->where('qty_pcs', '>', 0);
            }

            // Group by invoice
            $query->groupBy('customer_id', 'nama_customer_sistem', 'no_invoice');

            // Get total records
            $totalRecords = $query->count();

            // log the query
            Log::info('Query: ' . $query->toSql());

            // Pagination
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            
            $records = $query->offset($start)->limit($length)->get();

            // Check if any invoice has been netted
            foreach ($records as &$record) {
                $nettHeader = NettInvoiceHeader::where('no_invoice', $record->no_invoice)->first();
                if ($nettHeader) {
                    $record->nett_invoice = $nettHeader->invoice_value_nett;
                }
            }

            return response()->json([
                'draw' => intval($request->get('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $records,
            ]);
        } catch (\Throwable $th) {
            Log::error('Error in NettInvoiceController@getData: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Get invoice item details
     */
    public function getInvoiceDetail(Request $request)
    {
        try {
            $noInvoice = $request->no_invoice;
            
            $items = PajakKeluaranDetail::where('no_invoice', $noInvoice)
                ->select('kode_produk', 'qty_pcs', 'dpp', 'ppn', 'disc')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $items,
            ]);
        } catch (\Throwable $th) {
            Log::error('Error in NettInvoiceController@getInvoiceDetail: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of retur invoices (excluding already used ones)
     */
    public function getReturList(Request $request)
    {
        try {
            $query = DB::table('pajak_keluaran_details')
                ->select(
                    'customer_id as kode_pelanggan',
                    'nama_customer_sistem as nama_pelanggan',
                    'no_invoice',
                    DB::raw('ABS(SUM(dpp + ppn)) as nilai_retur')
                )
                ->where('is_checked', 1)
                ->where('is_downloaded', 0)
                ->where('qty_pcs', '<', 0);

            // Apply same filters as getData
            if ($request->has('pt') && !in_array('all', $request->pt ?? [])) {
                $query->whereIn('company', $request->pt);
            }

            if ($request->has('brand') && !in_array('all', $request->brand ?? [])) {
                $query->whereIn('brand', $request->brand);
            }

            if ($request->has('depo') && !in_array('all', $request->depo ?? [])) {
                $userInfo = getLoggedInUserInfo();
                
                if ($userInfo && !in_array('all', $userInfo->depo)) {
                    $allowedDepos = \App\Models\MasterDepo::whereIn('code', $userInfo->depo)
                        ->get()->pluck('name')->toArray();
                    $requestedDepos = \App\Models\MasterDepo::whereIn('code', $request->depo)
                        ->get()->pluck('name')->toArray();
                    $validDepos = array_intersect($requestedDepos, $allowedDepos);
                    
                    if (!empty($validDepos)) {
                        $query->whereIn('depo', $validDepos);
                    } else {
                        $query->whereRaw("1 = 0");
                    }
                } else {
                    $depos = \App\Models\MasterDepo::whereIn('code', $request->depo)
                        ->get()->pluck('name')->toArray();
                    $query->whereIn('depo', $depos);
                }
            }

            if ($request->has('periode') && !empty($request->periode)) {
                $periode = explode(' - ', $request->periode);
                if (count($periode) === 2) {
                    $periodeAwal = \Carbon\Carbon::createFromFormat('d/m/Y', $periode[0])->format('Y-m-d');
                    $periodeAkhir = \Carbon\Carbon::createFromFormat('d/m/Y', $periode[1])->format('Y-m-d');
                    $query->whereRaw("tgl_faktur_pajak >= '{$periodeAwal}' AND tgl_faktur_pajak <= '{$periodeAkhir}'");
                }
            }

            // Exclude retur that have been used in netting process
            $usedReturs = NettInvoiceDetail::pluck('no_invoice_retur')->toArray();
            if (!empty($usedReturs)) {
                $query->whereNotIn('no_invoice', $usedReturs);
            }

            // Group by invoice
            $query->groupBy('customer_id', 'nama_customer_sistem', 'no_invoice');

            $returs = $query->get();

            return response()->json([
                'status' => true,
                'data' => $returs,
            ]);
        } catch (\Throwable $th) {
            Log::error('Error in NettInvoiceController@getReturList: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Process netting between invoice and retur
     */
    public function processNett(Request $request)
    {
        try {
            DB::beginTransaction();

            $noInvoice = $request->no_invoice;
            $returInvoices = $request->retur_invoices ?? [];

            if (empty($returInvoices)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pilih minimal satu invoice retur',
                ], 400);
            }

            // Check if any retur has been used before
            $usedReturs = NettInvoiceDetail::whereIn('no_invoice_retur', $returInvoices)->pluck('no_invoice_retur')->toArray();
            if (!empty($usedReturs)) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Invoice retur berikut sudah pernah digunakan: ' . implode(', ', $usedReturs),
                ], 400);
            }

            // Get invoice data
            $invoiceItems = PajakKeluaranDetail::where('no_invoice', $noInvoice)->get();
            if ($invoiceItems->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Invoice tidak ditemukan',
                ], 404);
            }

            $invoiceValueOriginal = $invoiceItems->sum(function ($item) {
                return $item->dpp + $item->ppn;
            });

            // Get retur data and calculate total
            $totalReturValue = 0;
            foreach ($returInvoices as $returInvoice) {
                $returItems = PajakKeluaranDetail::where('no_invoice', $returInvoice)->get();
                $returValue = abs($returItems->sum(function ($item) {
                    return $item->dpp + $item->ppn;
                }));
                $totalReturValue += $returValue;
            }

            // Calculate nett value
            $invoiceValueNett = $invoiceValueOriginal - $totalReturValue;

            // Generate unique transaction ID
            $idTransaksi = 'NETT-' . date('YmdHis') . '-' . uniqid();

            $firstItem = $invoiceItems->first();

            // Save to nett_invoice_headers
            $nettHeader = NettInvoiceHeader::create([
                'id_transaksi' => $idTransaksi,
                'pt' => $firstItem->company,
                'principal' => $firstItem->brand,
                'depo' => $firstItem->depo,
                'no_invoice' => $noInvoice,
                'invoice_value_original' => $invoiceValueOriginal,
                'invoice_value_nett' => $invoiceValueNett,
                'mp_bulan' => date('m'),
                'mp_tahun' => date('Y'),
                'is_checked' => 1,
                'is_downloaded' => 0,
                'status' => 'netted',
                'created_at' => now(),
            ]);

            // Save invoice items to nett_invoice_header_items
            foreach ($invoiceItems as $item) {
                NettInvoiceHeaderItem::create([
                    'id_transaksi' => $idTransaksi,
                    'no_invoice' => $noInvoice,
                    'kode_barang' => $item->kode_produk,
                    'satuan' => $item->satuan,
                    'qty' => $item->qty_pcs,
                    'harga_satuan' => $item->hargasatuan_sblm_ppn,
                    'harga_total' => $item->hargatotal_sblm_ppn,
                    'created_at' => now(),
                ]);
            }

            // Save retur data to nett_invoice_details and items
            foreach ($returInvoices as $returInvoice) {
                $returItems = PajakKeluaranDetail::where('no_invoice', $returInvoice)->get();
                $returValue = abs($returItems->sum(function ($item) {
                    return $item->dpp + $item->ppn;
                }));

                $firstReturItem = $returItems->first();

                NettInvoiceDetail::create([
                    'id_transaksi' => $idTransaksi,
                    'pt' => $firstReturItem->company,
                    'principal' => $firstReturItem->brand,
                    'depo' => $firstReturItem->depo,
                    'no_invoice_retur' => $returInvoice,
                    'invoice_retur_value' => $returValue,
                    'mp_bulan' => date('m'),
                    'mp_tahun' => date('Y'),
                    'is_checked' => 1,
                    'is_downloaded' => 0,
                    'status' => 'used',
                    'created_at' => now(),
                ]);

                foreach ($returItems as $item) {
                    NettInvoiceDetailItem::create([
                        'id_transaksi' => $idTransaksi,
                        'no_invoice_retur' => $returInvoice,
                        'kode_barang' => $item->kode_produk,
                        'satuan' => $item->satuan,
                        'qty' => abs((float) $item->qty_pcs),
                        'harga_satuan' => abs((float) $item->hargasatuan_sblm_ppn),
                        'harga_total' => abs((float) $item->hargatotal_sblm_ppn),
                        'created_at' => now(),
                    ]);
                }
            }

            DB::commit();

            broadcast(new UserEvent(
                'success',
                'Nett Invoice',
                "Berhasil melakukan proses netting untuk invoice {$noInvoice}",
                Auth::user()
            ));

            return response()->json([
                'status' => true,
                'message' => 'Proses netting berhasil',
                'data' => [
                    'id_transaksi' => $idTransaksi,
                    'nett_value' => $invoiceValueNett,
                ],
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error in NettInvoiceController@processNett: ' . $th->getMessage());
            
            broadcast(new UserEvent(
                'error',
                'Nett Invoice',
                'Gagal melakukan proses netting: ' . $th->getMessage(),
                Auth::user()
            ));

            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Export netted invoices
     */
    public function exportData(Request $request)
    {
        try {
            $format = $request->get('format', 'xlsx');
            
            $writerType = $format === 'csv' 
                ? \Maatwebsite\Excel\Excel::CSV 
                : \Maatwebsite\Excel\Excel::XLSX;

            $headers = [
                'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ];

            $filename = 'nett_invoice_' . date('Ymd_His') . '.' . $format;

            return Excel::download(
                new NettInvoiceExport(),
                $filename,
                $writerType,
                $headers
            );
        } catch (\Throwable $th) {
            Log::error('Error in NettInvoiceController@exportData: ' . $th->getMessage());
            
            broadcast(new UserEvent(
                'error',
                'Nett Invoice Export',
                'Gagal export data: ' . $th->getMessage(),
                Auth::user()
            ));

            return redirect()->back()->with('error', 'Gagal export data');
        }
    }
}
