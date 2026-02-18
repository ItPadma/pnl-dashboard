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
use App\Models\NettInvoiceHistory;
use App\Models\PajakKeluaranDetail;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class NettInvoiceController extends Controller
{
    public function index()
    {
        return view('pnl.reguler.pajak-keluaran.nett-invoice.index');
    }

    /**
     * Get retur invoice data for the main DataTable
     */
    public function getData(Request $request)
    {
        try {
            $validatedFilters = $this->validateCommonFilters($request, true);
            $ptFilters = $validatedFilters['pt'];
            $brandFilters = $validatedFilters['brand'];
            $depoFilters = $validatedFilters['depo'];

            $query = DB::table('pajak_keluaran_details')
                ->select(
                    'customer_id as kode_pelanggan',
                    'nama_customer_sistem as nama_pelanggan',
                    'no_invoice',
                    'tgl_faktur_pajak',
                    DB::raw('ABS(SUM(dpp + ppn)) as nilai_retur'),
                )
                ->where('is_downloaded', 0)
                ->where(function ($subQuery) {
                    $subQuery
                        ->where(function ($innerQuery) {
                            $innerQuery
                                ->where('qty_pcs', '<', 0)
                                ->where('hargatotal_sblm_ppn', '>=', -1000000)
                                ->where('has_moved', 'n');
                        })
                        ->orWhere('moved_to', 'retur');
                });

            $this->applyCommonPajakKeluaranFilters(
                $query,
                $ptFilters,
                $brandFilters,
                $depoFilters,
                $validatedFilters['periode'] ?? null,
            );

            // Exclude retur that have been fully used in netting (remaining_value = 0)
            $fullyUsedReturs = NettInvoiceDetail::where('remaining_value', 0)
                ->pluck('no_invoice_retur')
                ->toArray();
            if (! empty($fullyUsedReturs)) {
                $query->whereNotIn('no_invoice', $fullyUsedReturs);
            }

            // Group by invoice
            $query->groupBy(
                'customer_id',
                'nama_customer_sistem',
                'no_invoice',
                'tgl_faktur_pajak',
            );

            // Get total records
            $countQuery = clone $query;
            $totalRecords = DB::table(DB::raw("({$countQuery->toSql()}) as sub"))
                ->mergeBindings($countQuery)
                ->count();

            // Pagination
            $start = (int) ($validatedFilters['start'] ?? 0);
            $length = (int) ($validatedFilters['length'] ?? 10);

            $records = $query->orderBy('tgl_faktur_pajak', 'asc')
                ->offset($start)
                ->limit($length)
                ->get();

            // Check for partial usage and adjust nilai_retur
            foreach ($records as &$record) {
                $partialDetail = NettInvoiceDetail::where(
                    'no_invoice_retur',
                    $record->no_invoice,
                )->where('remaining_value', '>', 0)->first();

                if ($partialDetail) {
                    $record->nilai_retur = $partialDetail->remaining_value;
                    $record->is_partial = true;
                } else {
                    $record->is_partial = false;
                }
            }

            return response()->json([
                'draw' => intval($request->get('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $records,
            ]);
        } catch (\Throwable $th) {
            if (
                $th instanceof ValidationException ||
                $th instanceof AuthorizationException
            ) {
                throw $th;
            }

            Log::error(
                'Error in NettInvoiceController@getData: '.$th->getMessage(),
            );

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Terjadi kesalahan internal.',
                    'data' => [],
                ],
                500,
            );
        }
    }

    /**
     * Get invoice item details
     */
    public function getInvoiceDetail(Request $request)
    {
        try {
            $validated = $request->validate([
                'no_invoice' => 'required|string|max:255',
            ]);

            $noInvoice = $validated['no_invoice'];

            $accessCheck = DB::table('pajak_keluaran_details')
                ->where('no_invoice', $noInvoice);
            $this->applyCommonPajakKeluaranFilters($accessCheck, ['all'], ['all'], ['all'], null);

            if (! $accessCheck->exists()) {
                throw new AuthorizationException('Anda tidak memiliki akses ke invoice ini.');
            }

            $itemsQuery = DB::table('pajak_keluaran_details')
                ->where('no_invoice', $noInvoice)
                ->select('kode_produk', 'qty_pcs', 'dpp', 'ppn', 'disc')
                ->orderBy('id');

            $this->applyCommonPajakKeluaranFilters($itemsQuery, ['all'], ['all'], ['all'], null);
            $items = $itemsQuery->get();

            return response()->json([
                'status' => true,
                'data' => $items,
            ]);
        } catch (\Throwable $th) {
            if (
                $th instanceof ValidationException ||
                $th instanceof AuthorizationException
            ) {
                throw $th;
            }

            Log::error(
                'Error in NettInvoiceController@getInvoiceDetail: '.
                    $th->getMessage(),
            );

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Terjadi kesalahan internal.',
                ],
                500,
            );
        }
    }

    /**
     * Get list of Non-PKP invoices for modal selection
     * Accepts retur_customer_ids to prioritize matching customers
     */
    public function getNonPkpList(Request $request)
    {
        try {
            $validatedFilters = $this->validateCommonFilters($request, false, true);
            $validatedExtra = $request->validate([
                'retur_customer_ids' => 'nullable|array|max:200',
                'retur_customer_ids.*' => 'string|max:255',
            ]);

            $ptFilters = $validatedFilters['pt'];
            $brandFilters = $validatedFilters['brand'];
            $depoFilters = $validatedFilters['depo'];
            $returCustomerIds = $validatedExtra['retur_customer_ids'] ?? [];

            $query = DB::table('pajak_keluaran_details')
                ->select(
                    'customer_id as kode_pelanggan',
                    'nama_customer_sistem as nama_pelanggan',
                    'no_invoice',
                    'tgl_faktur_pajak',
                    DB::raw('SUM(dpp + ppn) as nilai_invoice'),
                )
                ->where('is_downloaded', 0);

            $this->applyCommonPajakKeluaranFilters(
                $query,
                $ptFilters,
                $brandFilters,
                $depoFilters,
                $validatedFilters['periode'] ?? null,
            );

            // Force Non-PKP only
            $pkp = MasterPkp::where('is_active', true)->pluck('IDPelanggan')->toArray();
            $query->whereNotIn('customer_id', $pkp);
            $query->where('tipe_ppn', 'PPN');
            $query->where('qty_pcs', '>', 0);
            $query->where(function ($subQuery) {
                $subQuery
                    ->where('hargatotal_sblm_ppn', '>', 0)
                    ->orWhere('hargatotal_sblm_ppn', '<=', -1000000);
            });

            // Exclude invoices that have already been netted in selected reporting period only
            $reportingPeriod = $this->resolveReportingPeriodFromPeriode($validatedFilters['periode']);
            $nettedInvoices = NettInvoiceHeader::where('mp_bulan', $reportingPeriod['bulan'])
                ->where('mp_tahun', $reportingPeriod['tahun'])
                ->pluck('no_invoice')
                ->toArray();
            if (! empty($nettedInvoices)) {
                $query->whereNotIn('no_invoice', $nettedInvoices);
            }

            // Group by invoice
            $query->groupBy(
                'customer_id',
                'nama_customer_sistem',
                'no_invoice',
                'tgl_faktur_pajak',
            );

            $invoices = $query->orderBy('tgl_faktur_pajak', 'asc')->get();

            // Mark matching customer_ids for prioritization
            foreach ($invoices as &$invoice) {
                $invoice->is_matching_customer = in_array(
                    $invoice->kode_pelanggan,
                    $returCustomerIds,
                );
            }

            // Sort: matching customers first, then by date
            $sorted = $invoices->sortBy(function ($item) {
                return ($item->is_matching_customer ? '0' : '1').'_'.$item->tgl_faktur_pajak;
            })->values();

            return response()->json([
                'status' => true,
                'data' => $sorted,
            ]);
        } catch (\Throwable $th) {
            if (
                $th instanceof ValidationException ||
                $th instanceof AuthorizationException
            ) {
                throw $th;
            }

            Log::error(
                'Error in NettInvoiceController@getNonPkpList: '.$th->getMessage(),
            );

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Terjadi kesalahan internal.',
                ],
                500,
            );
        }
    }

    /**
     * Get list of retur invoices (legacy, kept for compatibility)
     */
    public function getReturList(Request $request)
    {
        try {
            $validatedFilters = $this->validateCommonFilters($request);
            $ptFilters = $validatedFilters['pt'];
            $brandFilters = $validatedFilters['brand'];
            $depoFilters = $validatedFilters['depo'];

            $query = DB::table('pajak_keluaran_details')
                ->select(
                    'customer_id as kode_pelanggan',
                    'nama_customer_sistem as nama_pelanggan',
                    'no_invoice',
                    DB::raw('ABS(SUM(dpp + ppn)) as nilai_retur'),
                )
                ->where('is_downloaded', 0)
                ->where(function ($subQuery) {
                    $subQuery
                        ->where(function ($innerQuery) {
                            $innerQuery
                                ->where('qty_pcs', '<', 0)
                                ->where('hargatotal_sblm_ppn', '>=', -1000000)
                                ->where('has_moved', 'n');
                        })
                        ->orWhere('moved_to', 'retur');
                });

            $this->applyCommonPajakKeluaranFilters(
                $query,
                $ptFilters,
                $brandFilters,
                $depoFilters,
                $validatedFilters['periode'] ?? null,
            );

            $fullyUsedReturs = NettInvoiceDetail::where('remaining_value', 0)
                ->pluck('no_invoice_retur')
                ->toArray();
            if (! empty($fullyUsedReturs)) {
                $query->whereNotIn('no_invoice', $fullyUsedReturs);
            }

            $query->groupBy(
                'customer_id',
                'nama_customer_sistem',
                'no_invoice',
            );

            $returs = $query->get();

            return response()->json([
                'status' => true,
                'data' => $returs,
            ]);
        } catch (\Throwable $th) {
            if (
                $th instanceof ValidationException ||
                $th instanceof AuthorizationException
            ) {
                throw $th;
            }

            Log::error(
                'Error in NettInvoiceController@getReturList: '.
                    $th->getMessage(),
            );

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Terjadi kesalahan internal.',
                ],
                500,
            );
        }
    }

    /**
     * Get available invoice dates for daterangepicker highlight.
     */
    public function getAvailableDates(Request $request)
    {
        try {
            $validatedFilters = $this->validateCommonFilters($request);

            $query = DB::table('pajak_keluaran_details')
                ->select(DB::raw('DISTINCT CAST(tgl_faktur_pajak AS DATE) as tanggal'))
                ->where('is_downloaded', 0)
                ->whereNotNull('tgl_faktur_pajak')
                ->where(function ($subQuery) {
                    $subQuery
                        ->where(function ($innerQuery) {
                            $innerQuery
                                ->where('qty_pcs', '<', 0)
                                ->where('hargatotal_sblm_ppn', '>=', -1000000)
                                ->where('has_moved', 'n');
                        })
                        ->orWhere('moved_to', 'retur');
                });

            $this->applyCommonPajakKeluaranFilters(
                $query,
                $validatedFilters['pt'],
                $validatedFilters['brand'],
                $validatedFilters['depo'],
                null,
            );

            $fullyUsedReturs = NettInvoiceDetail::where('remaining_value', 0)
                ->pluck('no_invoice_retur')
                ->toArray();
            if (! empty($fullyUsedReturs)) {
                $query->whereNotIn('no_invoice', $fullyUsedReturs);
            }

            $dates = $query->orderBy('tanggal', 'asc')->get();
            $formattedDates = $dates
                ->map(function ($item) {
                    return \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d');
                })
                ->toArray();

            return response()->json([
                'status' => true,
                'data' => $formattedDates,
            ]);
        } catch (\Throwable $th) {
            if (
                $th instanceof ValidationException ||
                $th instanceof AuthorizationException
            ) {
                throw $th;
            }

            Log::error(
                'Error in NettInvoiceController@getAvailableDates: '.$th->getMessage(),
            );

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Terjadi kesalahan internal.',
                    'data' => [],
                ],
                500,
            );
        }
    }

    /**
     * Process netting: multiple returs against multiple Non-PKP invoices
     * Returs are processed oldest-first, remaining value is tracked
     */
    public function processNett(Request $request)
    {
        try {
            $validated = $request->validate([
                'retur_invoices' => 'required|array|min:1|max:200',
                'retur_invoices.*' => 'string|max:255|distinct',
                'npkp_invoices' => 'required|array|min:1|max:200',
                'npkp_invoices.*' => 'string|max:255|distinct',
                'periode' => [
                    'required',
                    'string',
                    'regex:/^\d{2}\/\d{2}\/\d{4}\s-\s\d{2}\/\d{2}\/\d{4}$/',
                ],
            ]);

            $reportingPeriod = $this->resolveReportingPeriodFromPeriode($validated['periode']);
            $parsedPeriodeRange = $this->parsePeriodeRange($validated['periode']);
            $returInvoices = $validated['retur_invoices'];
            $npkpInvoices = $validated['npkp_invoices'];

            $this->assertInvoiceAccess($npkpInvoices);
            $this->assertInvoiceAccess($returInvoices);
            $this->assertNpkpInvoicesWithinPeriode($npkpInvoices, $parsedPeriodeRange);

            DB::beginTransaction();

            $alreadyNettedThisPeriod = NettInvoiceHeader::whereIn('no_invoice', $npkpInvoices)
                ->where('mp_bulan', $reportingPeriod['bulan'])
                ->where('mp_tahun', $reportingPeriod['tahun'])
                ->lockForUpdate()
                ->pluck('no_invoice')
                ->toArray();

            if (! empty($alreadyNettedThisPeriod)) {
                DB::rollBack();

                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Sebagian invoice Non-PKP sudah diproses pada masa lapor terpilih: '.implode(', ', $alreadyNettedThisPeriod),
                    ],
                    422,
                );
            }

            $pkp = MasterPkp::where('is_active', true)->pluck('IDPelanggan')->toArray();

            // Collect and sort retur data oldest-first
            $returDataList = [];
            foreach ($returInvoices as $returInvoice) {
                $returItems = PajakKeluaranDetail::where(
                    'no_invoice',
                    $returInvoice,
                )->get();

                if ($returItems->isEmpty()) {
                    DB::rollBack();

                    return response()->json(
                        [
                            'status' => false,
                            'message' => "Invoice retur {$returInvoice} tidak ditemukan",
                        ],
                        404,
                    );
                }

                $partialDetail = NettInvoiceDetail::where(
                    'no_invoice_retur',
                    $returInvoice,
                )->where('remaining_value', '>', 0)->first();

                $returValue = $partialDetail
                    ? $partialDetail->remaining_value
                    : abs($returItems->sum(function ($item) {
                        return $item->dpp + $item->ppn;
                    }));

                $returDataList[] = [
                    'no_invoice' => $returInvoice,
                    'items' => $returItems,
                    'value' => $returValue,
                    'remaining' => $returValue,
                    'tgl_faktur_pajak' => $returItems->first()->tgl_faktur_pajak ?? '1970-01-01',
                    'is_partial_reuse' => $partialDetail !== null,
                ];
            }

            usort($returDataList, function ($a, $b) {
                return strcmp($a['tgl_faktur_pajak'], $b['tgl_faktur_pajak']);
            });

            // Generate unique transaction ID
            $idTransaksi = 'NETT-'.date('YmdHis').'-'.uniqid();

            // Process each Non-PKP invoice
            foreach ($npkpInvoices as $noInvoiceNpkp) {
                $npkpItems = PajakKeluaranDetail::where(
                    'no_invoice',
                    $noInvoiceNpkp,
                )->get();

                if ($npkpItems->isEmpty()) {
                    DB::rollBack();

                    return response()->json(
                        [
                            'status' => false,
                            'message' => "Invoice Non-PKP {$noInvoiceNpkp} tidak ditemukan",
                        ],
                        404,
                    );
                }

                // Validate Non-PKP
                $invalidInvoice = $npkpItems->contains(function ($item) use ($pkp) {
                    $isNonPkpCustomer = ! in_array($item->customer_id, $pkp);
                    $isValidValue =
                        $item->hargatotal_sblm_ppn > 0 ||
                        $item->hargatotal_sblm_ppn <= -1000000;

                    return ! (
                        $isNonPkpCustomer &&
                        $item->tipe_ppn === 'PPN' &&
                        $item->qty_pcs > 0 &&
                        $isValidValue
                    );
                });

                if ($invalidInvoice) {
                    DB::rollBack();

                    return response()->json(
                        [
                            'status' => false,
                            'message' => "Invoice {$noInvoiceNpkp} bukan Non-PKP (PPN) yang valid",
                        ],
                        400,
                    );
                }

                $invoiceValueOriginal = $npkpItems->sum(function ($item) {
                    return $item->dpp + $item->ppn;
                });

                // Calculate total retur used for this Non-PKP
                $remainingNpkpValue = $invoiceValueOriginal;
                $totalReturUsedForThis = 0;
                $returUsageForThis = [];

                foreach ($returDataList as $index => &$returData) {
                    if ($returData['remaining'] <= 0 || $remainingNpkpValue <= 0) {
                        continue;
                    }

                    $returUsed = 0;
                    if ($remainingNpkpValue >= $returData['remaining']) {
                        $returUsed = $returData['remaining'];
                        $remainingNpkpValue -= $returData['remaining'];
                        $returData['remaining'] = 0;
                    } else {
                        $returUsed = $remainingNpkpValue;
                        $returData['remaining'] -= $remainingNpkpValue;
                        $remainingNpkpValue = 0;
                    }

                    $totalReturUsedForThis += $returUsed;
                    $returUsageForThis[] = [
                        'index' => $index,
                        'no_invoice' => $returData['no_invoice'],
                        'used' => $returUsed,
                        'remaining_after' => $returData['remaining'],
                    ];

                    if ($remainingNpkpValue <= 0) {
                        break;
                    }
                }
                unset($returData);

                $invoiceValueNett = $invoiceValueOriginal - $totalReturUsedForThis;
                $firstNpkpItem = $npkpItems->first();

                // Save to nett_invoice_headers
                NettInvoiceHeader::create([
                    'id_transaksi' => $idTransaksi,
                    'pt' => $firstNpkpItem->company,
                    'principal' => $firstNpkpItem->brand,
                    'depo' => $firstNpkpItem->depo,
                    'no_invoice' => $noInvoiceNpkp,
                    'invoice_value_original' => $invoiceValueOriginal,
                    'invoice_value_nett' => $invoiceValueNett,
                    'mp_bulan' => $reportingPeriod['bulan'],
                    'mp_tahun' => $reportingPeriod['tahun'],
                    'is_checked' => 1,
                    'is_downloaded' => 0,
                    'status' => 'netted',
                    'created_at' => now(),
                ]);

                // Save Non-PKP invoice items
                foreach ($npkpItems as $item) {
                    NettInvoiceHeaderItem::create([
                        'id_transaksi' => $idTransaksi,
                        'no_invoice' => $noInvoiceNpkp,
                        'kode_barang' => $item->kode_produk,
                        'satuan' => $item->satuan,
                        'qty' => $item->qty_pcs,
                        'harga_satuan' => $item->hargasatuan_sblm_ppn,
                        'harga_total' => $item->hargatotal_sblm_ppn,
                        'created_at' => now(),
                    ]);
                }

                // Save history for each retur usage against this Non-PKP
                foreach ($returUsageForThis as $usage) {
                    NettInvoiceHistory::create([
                        'id_transaksi' => $idTransaksi,
                        'no_invoice_npkp' => $noInvoiceNpkp,
                        'no_invoice_retur' => $usage['no_invoice'],
                        'nilai_invoice_npkp' => $invoiceValueOriginal,
                        'nilai_retur_used' => $usage['used'],
                        'nilai_nett' => $invoiceValueNett,
                        'remaining_value' => $usage['remaining_after'],
                        'status' => 'processed',
                        'created_by' => Auth::user()->username ?? Auth::user()->name ?? null,
                        'created_at' => now(),
                    ]);
                }

                // If no retur remaining, stop processing more Non-PKP invoices
                $totalReturRemaining = array_sum(array_column($returDataList, 'remaining'));
                if ($totalReturRemaining <= 0) {
                    break;
                }
            }

            // Save retur details and items
            foreach ($returDataList as $returData) {
                $firstReturItem = $returData['items']->first();
                $finalRemaining = $returData['remaining'];
                $detailStatus = $finalRemaining > 0 ? 'partial' : 'used';

                if ($returData['is_partial_reuse']) {
                    NettInvoiceDetail::where(
                        'no_invoice_retur',
                        $returData['no_invoice'],
                    )->update([
                        'remaining_value' => $finalRemaining,
                        'status' => $detailStatus,
                    ]);
                } else {
                    NettInvoiceDetail::create([
                        'id_transaksi' => $idTransaksi,
                        'pt' => $firstReturItem->company,
                        'principal' => $firstReturItem->brand,
                        'depo' => $firstReturItem->depo,
                        'no_invoice_retur' => $returData['no_invoice'],
                        'invoice_retur_value' => $returData['value'],
                        'remaining_value' => $finalRemaining,
                        'mp_bulan' => $reportingPeriod['bulan'],
                        'mp_tahun' => $reportingPeriod['tahun'],
                        'is_checked' => 1,
                        'is_downloaded' => 0,
                        'status' => $detailStatus,
                        'created_at' => now(),
                    ]);

                    foreach ($returData['items'] as $item) {
                        NettInvoiceDetailItem::create([
                            'id_transaksi' => $idTransaksi,
                            'no_invoice_retur' => $returData['no_invoice'],
                            'kode_barang' => $item->kode_produk,
                            'satuan' => $item->satuan,
                            'qty' => abs((float) $item->qty_pcs),
                            'harga_satuan' => abs(
                                (float) $item->hargasatuan_sblm_ppn,
                            ),
                            'harga_total' => abs(
                                (float) $item->hargatotal_sblm_ppn,
                            ),
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Proses netting berhasil',
                'data' => [
                    'id_transaksi' => $idTransaksi,
                ],
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException || $th instanceof AuthorizationException) {
                throw $th;
            }

            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            Log::error(
                'Error in NettInvoiceController@processNett: '.
                    $th->getMessage(),
            );

            broadcast(
                new UserEvent(
                    'error',
                    'Nett Invoice',
                    'Gagal melakukan proses netting.',
                    Auth::user(),
                ),
            );

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Terjadi kesalahan internal.',
                ],
                500,
            );
        }
    }

    /**
     * Get nett invoice processing history
     */
    public function getNettHistory(Request $request)
    {
        try {
            $query = DB::table('nett_invoice_histories')
                ->select('nett_invoice_histories.*')
                ->join('nett_invoice_headers', function ($join) {
                    $join->on('nett_invoice_histories.id_transaksi', '=', 'nett_invoice_headers.id_transaksi')
                        ->on('nett_invoice_histories.no_invoice_npkp', '=', 'nett_invoice_headers.no_invoice');
                });

            $this->applyDepoFilterWithAccessGuard($query, ['all']);

            $totalRecords = $query->count();

            $start = $request->get('start', 0);
            $length = $request->get('length', 10);

            $records = $query->orderBy('nett_invoice_histories.created_at', 'desc')
                ->offset($start)
                ->limit($length)
                ->get();

            return response()->json([
                'draw' => intval($request->get('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $records,
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof AuthorizationException) {
                throw $th;
            }

            Log::error(
                'Error in NettInvoiceController@getNettHistory: '.$th->getMessage(),
            );

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Terjadi kesalahan internal.',
                    'data' => [],
                ],
                500,
            );
        }
    }

    /**
     * Export netted invoices
     */
    public function exportData(Request $request)
    {
        try {
            $format = $request->get('format', 'xlsx');

            $writerType =
                $format === 'csv'
                    ? \Maatwebsite\Excel\Excel::CSV
                    : \Maatwebsite\Excel\Excel::XLSX;

            $headers = [
                'Content-Type' => $format === 'csv'
                        ? 'text/csv'
                        : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ];

            $filename = 'nett_invoice_'.date('Ymd_His').'.'.$format;

            return Excel::download(
                new NettInvoiceExport,
                $filename,
                $writerType,
                $headers,
            );
        } catch (\Throwable $th) {
            Log::error(
                'Error in NettInvoiceController@exportData: '.
                    $th->getMessage(),
            );

            broadcast(
                new UserEvent(
                    'error',
                    'Nett Invoice Export',
                    'Gagal export data: '.$th->getMessage(),
                    Auth::user(),
                ),
            );

            return redirect()->back()->with('error', 'Gagal export data');
        }
    }

    /**
     * @return array<int, string>
     */
    private function normalizeFilterValues(mixed $filters): array
    {
        if (! is_array($filters) || empty($filters)) {
            return ['all'];
        }

        $normalized = array_values(array_filter($filters, fn ($value) => is_string($value) && $value !== ''));

        return empty($normalized) ? ['all'] : $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCommonFilters(
        Request $request,
        bool $withPagination = false,
        bool $requirePeriode = false,
    ): array {
        $rules = [
            'pt' => 'nullable|array|max:100',
            'pt.*' => 'string|max:50',
            'brand' => 'nullable|array|max:100',
            'brand.*' => 'string|max:100',
            'depo' => 'nullable|array|max:100',
            'depo.*' => 'string|max:50',
            'periode' => [
                $requirePeriode ? 'required' : 'nullable',
                'string',
                'regex:/^\d{2}\/\d{2}\/\d{4}\s-\s\d{2}\/\d{2}\/\d{4}$/',
            ],
        ];

        if ($withPagination) {
            $rules['draw'] = 'nullable|integer|min:0';
            $rules['start'] = 'nullable|integer|min:0';
            $rules['length'] = 'nullable|integer|min:1|max:100';
        }

        $validated = $request->validate($rules);

        if (! empty($validated['periode'])) {
            $this->parsePeriodeRange($validated['periode']);
        }

        $validated['pt'] = $this->normalizeFilterValues($validated['pt'] ?? ['all']);
        $validated['brand'] = $this->normalizeFilterValues($validated['brand'] ?? ['all']);
        $validated['depo'] = $this->normalizeFilterValues($validated['depo'] ?? ['all']);

        return $validated;
    }

    /**
     * Apply shared pt/brand/depo/periode filters with depo access guard.
     */
    private function applyCommonPajakKeluaranFilters(
        Builder $query,
        array $ptFilters,
        array $brandFilters,
        array $depoFilters,
        ?string $periode,
    ): void {
        if (! in_array('all', $ptFilters, true)) {
            $query->whereIn('company', $ptFilters);
        }

        if (! in_array('all', $brandFilters, true)) {
            $query->whereIn('brand', $brandFilters);
        }

        $this->applyDepoFilterWithAccessGuard($query, $depoFilters);

        if (! empty($periode)) {
            $parsedPeriode = $this->parsePeriodeRange($periode);
            $query->whereBetween('tgl_faktur_pajak', $parsedPeriode);
        }
    }

    /**
     * Enforce user depo access and intersect with requested depo filter.
     */
    private function applyDepoFilterWithAccessGuard(Builder $query, array $depoFilters): void
    {
        $userInfo = getLoggedInUserInfo();
        $userDepoCodes = $userInfo?->depo;

        if (! is_array($userDepoCodes) || empty($userDepoCodes)) {
            throw new AuthorizationException('Akses depo tidak valid.');
        }

        $hasAllDepoAccess = in_array('all', $userDepoCodes, true);

        if ($hasAllDepoAccess) {
            if (! in_array('all', $depoFilters, true)) {
                $requestedDepos = $this->resolveDepoNamesByCodes($depoFilters);

                if (! empty($requestedDepos)) {
                    $query->whereIn('depo', $requestedDepos);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

            return;
        }

        $allowedDepos = $this->resolveDepoNamesByCodes($userDepoCodes);
        if (empty($allowedDepos)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('depo', $allowedDepos);

        if (! in_array('all', $depoFilters, true)) {
            $requestedDepos = $this->resolveDepoNamesByCodes($depoFilters);
            $validDepos = array_values(array_intersect($requestedDepos, $allowedDepos));

            if (! empty($validDepos)) {
                $query->whereIn('depo', $validDepos);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function resolveDepoNamesByCodes(array $depoCodes): array
    {
        $codes = array_values(array_filter($depoCodes, fn ($code) => is_string($code) && $code !== '' && $code !== 'all'));
        if (empty($codes)) {
            return [];
        }

        return MasterDepo::whereIn('code', $codes)
            ->pluck('name')
            ->toArray();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parsePeriodeRange(string $periode): array
    {
        $parts = explode(' - ', $periode);
        if (count($parts) !== 2) {
            throw ValidationException::withMessages([
                'periode' => ['Format periode tidak valid. Gunakan dd/mm/YYYY - dd/mm/YYYY.'],
            ]);
        }

        try {
            $start = \Carbon\Carbon::createFromFormat('d/m/Y', $parts[0]);
            $end = \Carbon\Carbon::createFromFormat('d/m/Y', $parts[1]);

            if ($start->format('d/m/Y') !== $parts[0] || $end->format('d/m/Y') !== $parts[1]) {
                throw ValidationException::withMessages([
                    'periode' => ['Tanggal periode tidak valid.'],
                ]);
            }

            if ($start->greaterThan($end)) {
                throw ValidationException::withMessages([
                    'periode' => ['Tanggal mulai periode tidak boleh lebih besar dari tanggal akhir.'],
                ]);
            }

            return [$start->format('Y-m-d'), $end->format('Y-m-d')];
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'periode' => ['Tanggal periode tidak valid.'],
            ]);
        }
    }

    /**
     * @return array{bulan: string, tahun: string}
     */
    private function resolveReportingPeriodFromPeriode(string $periode): array
    {
        [, $endDate] = $this->parsePeriodeRange($periode);

        $reportingDate = Carbon::createFromFormat('Y-m-d', $endDate);
        if ((int) $reportingDate->format('d') <= 15) {
            $reportingDate = $reportingDate->subMonthNoOverflow();
        }

        return [
            'bulan' => $reportingDate->format('m'),
            'tahun' => $reportingDate->format('Y'),
        ];
    }

    /**
     * @param  array<int, string>  $invoiceNumbers
     */
    private function assertInvoiceAccess(array $invoiceNumbers): void
    {
        if (empty($invoiceNumbers)) {
            return;
        }

        $accessibleInvoicesQuery = DB::table('pajak_keluaran_details')
            ->select('no_invoice')
            ->whereIn('no_invoice', $invoiceNumbers)
            ->groupBy('no_invoice');

        $this->applyDepoFilterWithAccessGuard($accessibleInvoicesQuery, ['all']);

        $accessibleInvoices = $accessibleInvoicesQuery->pluck('no_invoice')->toArray();
        $requestedInvoices = array_values(array_unique($invoiceNumbers));

        if (count($accessibleInvoices) !== count($requestedInvoices)) {
            throw new AuthorizationException('Sebagian invoice tidak termasuk akses depo Anda.');
        }
    }

    /**
     * @param  array<int, string>  $invoiceNumbers
     * @param  array{0: string, 1: string}  $periodeRange
     */
    private function assertNpkpInvoicesWithinPeriode(array $invoiceNumbers, array $periodeRange): void
    {
        if (empty($invoiceNumbers)) {
            return;
        }

        $pkp = MasterPkp::where('is_active', true)->pluck('IDPelanggan')->toArray();

        $query = DB::table('pajak_keluaran_details')
            ->select('no_invoice')
            ->whereIn('no_invoice', $invoiceNumbers)
            ->where('is_downloaded', 0)
            ->whereBetween('tgl_faktur_pajak', $periodeRange)
            ->whereNotIn('customer_id', $pkp)
            ->where('tipe_ppn', 'PPN')
            ->where('qty_pcs', '>', 0)
            ->where(function ($subQuery) {
                $subQuery
                    ->where('hargatotal_sblm_ppn', '>', 0)
                    ->orWhere('hargatotal_sblm_ppn', '<=', -1000000);
            })
            ->groupBy('no_invoice');

        $this->applyDepoFilterWithAccessGuard($query, ['all']);

        $eligibleInvoices = $query->pluck('no_invoice')->toArray();
        $requestedInvoices = array_values(array_unique($invoiceNumbers));

        if (count($eligibleInvoices) !== count($requestedInvoices)) {
            throw ValidationException::withMessages([
                'npkp_invoices' => ['Sebagian invoice Non-PKP berada di luar periode terpilih atau tidak valid.'],
            ]);
        }
    }
}
