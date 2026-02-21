<?php

namespace App\Http\Controllers\PNL;

use App\Events\UserEvent;
use App\Exports\PajakKeluaranDetailExport;
use App\Exports\PajakKeluaranTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilities\LogController;
use App\Imports\PajakMasukanCoretaxImport;
use App\Models\AccessGroup;
use App\Models\MasterDepo;
use App\Models\MasterPkp;
use App\Models\NettInvoiceHeader;
use App\Models\PajakKeluaranDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class RegulerController extends Controller
{
    public function pkIndex()
    {
        return view("pnl.reguler.pajak-keluaran.index");
    }

    public function pkDbIndex()
    {
        return view("pnl.reguler.pajak-keluaran.index_db");
    }

    public function pmIndex()
    {
        return view("pnl.reguler.pajak-masukan.index");
    }

    public function pmUploadCsvIndex()
    {
        return view("pnl.reguler.pajak-masukan.upload");
    }

    public function dtPKGetData(Request $request)
    {
        if (!Auth::guard("web")->check()) {
            return response()->json(
                [
                    "status" => false,
                    "message" => "Unauthorized",
                    "data" => [],
                ],
                401,
            );
        }

        try {
            $draw = $request->get("draw");
            $start = $request->get("start");
            $rowperpage = $request->get("length");
            $columnIndex = $request->get("order")[0]["column"] ?? 0;
            $columnName =
                $request->get("columns")[$columnIndex]["data"] ??
                "tgl_faktur_pajak";
            $columnSortOrder = $request->get("order")[0]["dir"] ?? "desc";
            $columnName = $this->resolveSortableColumn($columnName);
            $columnSortOrder =
                strtolower($columnSortOrder) === "asc" ? "asc" : "desc";
            $searchValue = $request->get("search")["value"] ?? "";
            $tipe = "";
            $chstatus = "";
            $retrieve_count = 0;
            // Query base
            $dbquery = DB::table("pajak_keluaran_details")->select("*");
            $filters = $this->applyFilters($dbquery, $request);
            Log::info("periode: " . $request->periode);

            // Retrieve from live if no records found
            while ($retrieve_count == 0 && $dbquery->count() == 0) {
                Log::info(
                    "No records found in database, please sync from live",
                );
                broadcast(
                    new UserEvent(
                        "info",
                        "Info",
                        "Record tidak ditemukan di database, Silahkan lakukan sinkronisasi data.",
                        Auth::user()->id,
                    ),
                );
                // PajakKeluaranDetail::getFromLive($request->pt, $request->brand, $request->depo, $filters['periode_awal'], $filters['periode_akhir'], $filters['tipe'], $filters['chstatus']);
                $retrieve_count = 1;
            }
            // Total records
            $totalRecords = $dbquery->count();
            $totalRecordswithFilter = $dbquery->count();
            $records = $dbquery
                ->orderBy($columnName, $columnSortOrder)
                ->skip($start)
                ->take($rowperpage)
                ->get();

            if ($request->tipe === "nonstandar") {
                $records = $records->map(function ($record) {
                    $record->nonstandar_keterangan = $this->buildNonStandarReason(
                        $record->nik ?? null,
                        $record->has_moved ?? null,
                        $record->moved_to ?? null,
                        $record->tipe_ppn ?? null,
                        $record->qty_pcs ?? null,
                        $record->hargatotal_sblm_ppn ?? null,
                        $record->customer_id ?? null,
                    );

                    return $record;
                });
            }

            return response()->json([
                "draw" => intval($draw),
                "iTotalRecords" => $totalRecords,
                "iTotalDisplayRecords" => $totalRecordswithFilter,
                "aaData" => $records,
                "status" => true,
            ]);
        } catch (\Throwable $th) {
            Log::error("Failed to load pajak keluaran data", [
                "context" => __METHOD__,
                "exception" => $th,
            ]);

            return response()->json(
                [
                    "status" => false,
                    "message" => "Terjadi kesalahan saat memuat data.",
                    "data" => [],
                ],
                500,
            );
        }
    }

    public function dtPKDbGetData(Request $request)
    {
        if (!Auth::guard("web")->check()) {
            return response()->json(
                [
                    "status" => false,
                    "message" => "Unauthorized",
                    "data" => [],
                ],
                401,
            );
        }

        try {
            $draw = $request->get("draw");
            $start = $request->get("start");
            $rowperpage = $request->get("length");
            $columnIndex = $request->get("order")[0]["column"] ?? 0;
            $columnName =
                $request->get("columns")[$columnIndex]["data"] ??
                "tgl_faktur_pajak";
            $columnSortOrder = $request->get("order")[0]["dir"] ?? "desc";
            $columnName = $this->resolveSortableColumn($columnName);
            $columnSortOrder =
                strtolower($columnSortOrder) === "asc" ? "asc" : "desc";
            $searchValue = $request->get("search")["value"] ?? "";
            $grouped = $request->get("grouped") ?? false;

            // Query base
            $dbquery = DB::table("pajak_keluaran_details")->select("*");
            $this->applyFilters($dbquery, $request);
            Log::info("periode (DB only): " . $request->periode);

            if ($grouped) {
                // Get all records for grouping
                $allRecords = $dbquery
                    ->orderBy($columnName, $columnSortOrder)
                    ->get();

                // Group by invoice
                $groupedData = [];
                foreach ($allRecords as $record) {
                    $invoiceKey = $record->no_invoice;

                    if (!isset($groupedData[$invoiceKey])) {
                        // Create invoice-level record
                        $groupedData[$invoiceKey] = [
                            "no_invoice" => $record->no_invoice,
                            "customer_id" => $record->customer_id,
                            "nik" => $record->nik,
                            "nama_customer_sistem" =>
                                $record->nama_customer_sistem,
                            "npwp_customer" => $record->npwp_customer,
                            "no_do" => $record->no_do,
                            "tgl_faktur_pajak" => $record->tgl_faktur_pajak,
                            "alamat_sistem" => $record->alamat_sistem,
                            "type_pajak" => $record->type_pajak,
                            "nama_sesuai_npwp" => $record->nama_sesuai_npwp,
                            "alamat_npwp_lengkap" =>
                                $record->alamat_npwp_lengkap,
                            "no_telepon" => $record->no_telepon,
                            "no_fp" => $record->no_fp,
                            "brand" => $record->brand,
                            "depo" => $record->depo,
                            "area" => $record->area,
                            "type_jual" => $record->type_jual,
                            "kode_jenis_fp" => $record->kode_jenis_fp,
                            "fp_normal_pengganti" =>
                                $record->fp_normal_pengganti,
                            "id_tku_pembeli" => $record->id_tku_pembeli,
                            "barang_jasa" => $record->barang_jasa,
                            "is_checked" => $record->is_checked,
                            "is_downloaded" => $record->is_downloaded,
                            "has_moved" => $record->has_moved,
                            "moved_to" => $record->moved_to,
                            "nonstandar_keterangan" => $this->buildNonStandarReason(
                                $record->nik ?? null,
                                $record->has_moved ?? null,
                                $record->moved_to ?? null,
                                $record->tipe_ppn ?? null,
                                $record->qty_pcs ?? null,
                                $record->hargatotal_sblm_ppn ?? null,
                                $record->customer_id ?? null,
                            ),
                            "total_hargatotal" => 0,
                            "total_disc" => 0,
                            "total_dpp" => 0,
                            "total_dpp_lain" => 0,
                            "total_ppn" => 0,
                            "products" => [],
                        ];
                    }

                    // Add product to invoice
                    $groupedData[$invoiceKey]["products"][] = [
                        "kode_produk" => $record->kode_produk,
                        "nama_produk" => $record->nama_produk,
                        "satuan" => $record->satuan,
                        "qty_pcs" => $record->qty_pcs,
                        "hargasatuan_sblm_ppn" => $record->hargasatuan_sblm_ppn,
                        "hargatotal_sblm_ppn" => $record->hargatotal_sblm_ppn,
                        "disc" => $record->disc,
                        "dpp" => $record->dpp,
                        "dpp_lain" => $record->dpp_lain,
                        "ppn" => $record->ppn,
                    ];

                    // Accumulate totals
                    $groupedData[$invoiceKey]["total_hargatotal"] += floatval(
                        $record->hargatotal_sblm_ppn ?? 0,
                    );
                    $groupedData[$invoiceKey]["total_disc"] += floatval(
                        $record->disc ?? 0,
                    );
                    $groupedData[$invoiceKey]["total_dpp"] += floatval(
                        $record->dpp ?? 0,
                    );
                    $groupedData[$invoiceKey]["total_dpp_lain"] += floatval(
                        $record->dpp_lain ?? 0,
                    );
                    $groupedData[$invoiceKey]["total_ppn"] += floatval(
                        $record->ppn ?? 0,
                    );
                }

                // Convert to indexed array
                $records = array_values($groupedData);

                // Enrich with nett invoice data for Non-PKP tab
                if ($request->tipe == "npkp") {
                    $invoiceNumbers = array_column($records, "no_invoice");
                    $nettHeaders = NettInvoiceHeader::whereIn(
                        "no_invoice",
                        $invoiceNumbers,
                    )
                        ->pluck("invoice_value_nett", "no_invoice")
                        ->toArray();

                    foreach ($records as &$record) {
                        if (isset($nettHeaders[$record["no_invoice"]])) {
                            $record["nett_dpp_ppn"] = floatval(
                                $nettHeaders[$record["no_invoice"]],
                            );
                            $record["is_netted"] = true;
                        } else {
                            $record["nett_dpp_ppn"] = null;
                            $record["is_netted"] = false;
                        }
                    }
                    unset($record);
                }

                $totalRecords = count($records);
                $totalRecordswithFilter = count($records);

                // Apply pagination
                $records = array_slice($records, $start, $rowperpage);
            } else {
                // Original ungrouped logic
                $totalRecords = $dbquery->count();
                $totalRecordswithFilter = $dbquery->count();
                $records = $dbquery
                    ->orderBy($columnName)
                    ->skip($start)
                    ->take($rowperpage)
                    ->get();

                if ($request->tipe === "nonstandar") {
                    $records = $records->map(function ($record) {
                        $record->nonstandar_keterangan = $this->buildNonStandarReason(
                            $record->nik ?? null,
                            $record->has_moved ?? null,
                            $record->moved_to ?? null,
                            $record->tipe_ppn ?? null,
                            $record->qty_pcs ?? null,
                            $record->hargatotal_sblm_ppn ?? null,
                            $record->customer_id ?? null,
                        );

                        return $record;
                    });
                }
            }

            return response()->json([
                "draw" => intval($draw),
                "iTotalRecords" => $totalRecords,
                "iTotalDisplayRecords" => $totalRecordswithFilter,
                "aaData" => $records,
                "status" => true,
            ]);
        } catch (\Throwable $th) {
            Log::error("Failed to load pajak keluaran grouped data", [
                "context" => __METHOD__,
                "exception" => $th,
            ]);

            return response()->json(
                [
                    "status" => false,
                    "message" => "Terjadi kesalahan saat memuat data.",
                    "data" => [],
                ],
                500,
            );
        }
    }

    public function updateMove2(Request $request)
    {
        if (!Auth::guard("web")->check()) {
            return response()->json(
                [
                    "status" => false,
                    "message" => "Unauthorized",
                    "data" => [],
                ],
                401,
            );
        }

        if (!$this->hasWriteAccess()) {
            return response()->json(
                [
                    "status" => false,
                    "message" => "Forbidden",
                    "data" => [],
                ],
                403,
            );
        }

        $validated = $request->validate([
            "ids" => "required|array|min:1",
            "ids.*" => "integer|distinct|exists:pajak_keluaran_details,id",
            "move_from" =>
                "required|in:pkp,pkpnppn,npkp,npkpnppn,retur,nonstandar",
            "move_to" =>
                "required|in:pkp,pkpnppn,npkp,npkpnppn,retur,nonstandar|different:move_from",
        ]);

        try {
            $ids = $validated["ids"];
            $moveFrom = $validated["move_from"];
            $moveTo = $validated["move_to"];

            DB::transaction(function () use (
                $request,
                $ids,
                $moveFrom,
                $moveTo,
            ): void {
                $items = $this->buildScopedPajakKeluaranQuery($request)
                    ->whereIn("id", $ids)
                    ->get();

                $matchedIds = $items->pluck("id")->all();
                $missingIds = array_values(array_diff($ids, $matchedIds));
                if (!empty($missingIds)) {
                    throw ValidationException::withMessages([
                        "ids" =>
                            "Sebagian data tidak ditemukan atau tidak memiliki akses.",
                    ]);
                }

                foreach ($items as $item) {
                    $item->has_moved = "y";
                    $item->moved_to = $moveTo;
                    $item->moved_at = now();
                    /** @var \App\Models\PajakKeluaranDetail $item */
                    $item->save();
                    LogController::createLog(
                        Auth::user()->id,
                        "Move Item from " . $moveFrom . " to " . $moveTo,
                        "Update",
                        "{id: " .
                            $item->id .
                            ", no_invoice: " .
                            $item->no_invoice .
                            ", no_do: " .
                            $item->no_do .
                            ", kode_produk: " .
                            $item->kode_produk .
                            ", move_from: " .
                            $moveFrom .
                            ", move_to: " .
                            $moveTo .
                            "}",
                        "pajak_keluaran_details",
                        "info",
                        $request,
                    );
                }
            });

            return response()->json([
                "status" => true,
                "message" => "Item moved successfully",
                "data" => [
                    "ids" => $ids,
                    "move_to" => $moveTo,
                ],
            ]);
        } catch (ValidationException $th) {
            throw $th;
        } catch (\Throwable $th) {
            Log::error("Failed to move pajak keluaran items", [
                "context" => __METHOD__,
                "exception" => $th,
                "payload" => $request->except(["_token"]),
            ]);

            return response()->json(
                [
                    "status" => false,
                    "message" => "Terjadi kesalahan saat memindahkan data.",
                    "data" => [],
                ],
                500,
            );
        }
    }

    public function checkMissingPkp(Request $request)
    {
        if (!Auth::guard("web")->check()) {
            return response()->json(
                [
                    "status" => false,
                    "message" => "Unauthorized",
                    "data" => [],
                ],
                401,
            );
        }

        $validated = $request->validate([
            "ids" => "required|array|min:1",
            "ids.*" => "integer|distinct|exists:pajak_keluaran_details,id",
        ]);

        try {
            $ids = $validated["ids"];

            // Get selected items
            $items = $this->buildScopedPajakKeluaranQuery($request)
                ->select(
                    "customer_id",
                    "nama_customer_sistem",
                    "alamat_sistem",
                    "npwp_customer",
                )
                ->whereIn("id", $ids)
                ->whereNotNull("customer_id")
                ->where("customer_id", "!=", "")
                ->get()
                ->unique("customer_id");

            $customerIds = $items->pluck("customer_id")->toArray();

            // Find missing from master_pkp
            $existingPkpCustomerIds = MasterPkp::whereIn(
                "IDPelanggan",
                $customerIds,
            )
                ->where("is_active", true)
                ->pluck("IDPelanggan")
                ->toArray();

            $missingPkp = $items
                ->filter(function ($item) use ($existingPkpCustomerIds) {
                    return !in_array(
                        $item->customer_id,
                        $existingPkpCustomerIds,
                    );
                })
                ->values()
                ->map(function ($item) {
                    return [
                        "IDPelanggan" => $item->customer_id,
                        "NamaPKP" => $item->nama_customer_sistem,
                        "AlamatPKP" => $item->alamat_sistem,
                        "NoPKP" => $item->npwp_customer,
                        "TypePajak" => "PPN", // Default to PPN
                    ];
                });

            return response()->json([
                "status" => true,
                "message" => "Check missing PKP successful",
                "data" => $missingPkp,
            ]);
        } catch (\Throwable $th) {
            Log::error("Failed to check missing PKP", [
                "context" => __METHOD__,
                "exception" => $th,
            ]);

            return response()->json(
                [
                    "status" => false,
                    "message" => "Terjadi kesalahan saat memeriksa data PKP.",
                    "data" => [],
                ],
                500,
            );
        }
    }

    public function saveMasterPkpBulk(Request $request)
    {
        if (!Auth::guard("web")->check()) {
            return response()->json(
                [
                    "status" => false,
                    "message" => "Unauthorized",
                ],
                401,
            );
        }

        if (!$this->hasWriteAccess()) {
            return response()->json(
                [
                    "status" => false,
                    "message" => "Forbidden",
                ],
                403,
            );
        }

        $validated = $request->validate([
            "pkp_list" => "required|array|min:1",
            "pkp_list.*.IDPelanggan" => "required|string",
            "pkp_list.*.NamaPKP" => "required|string",
            "pkp_list.*.AlamatPKP" => "nullable|string",
            "pkp_list.*.NoPKP" => "nullable|string",
            "pkp_list.*.TypePajak" => "required|string",
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                foreach ($validated["pkp_list"] as $pkpData) {
                    MasterPkp::updateOrCreate(
                        ["IDPelanggan" => $pkpData["IDPelanggan"]],
                        [
                            "NamaPKP" => $pkpData["NamaPKP"],
                            "AlamatPKP" => $pkpData["AlamatPKP"],
                            "NoPKP" => $pkpData["NoPKP"],
                            "TypePajak" => $pkpData["TypePajak"],
                            "is_active" => true,
                        ],
                    );

                    LogController::createLog(
                        Auth::user()->id,
                        "Add/Update Master PKP via Modal",
                        "Create",
                        json_encode($pkpData),
                        "master_pkp",
                        "info",
                        $request,
                    );
                }
            });

            return response()->json([
                "status" => true,
                "message" => "Data Master PKP berhasil disimpan.",
            ]);
        } catch (\Throwable $th) {
            Log::error("Failed to save bulk Master PKP", [
                "context" => __METHOD__,
                "exception" => $th,
            ]);

            return response()->json(
                [
                    "status" => false,
                    "message" =>
                        "Terjadi kesalahan saat menyimpan data Master PKP.",
                ],
                500,
            );
        }
    }

    public function updateChecked(Request $request)
    {
        if (!Auth::guard("web")->check()) {
            return response()->json(
                [
                    "status" => false,
                    "message" => "Unauthorized",
                ],
                401,
            );
        }

        if (!$this->hasWriteAccess()) {
            return response()->json(
                [
                    "status" => false,
                    "message" => "Forbidden",
                ],
                403,
            );
        }

        $validated = $request->validate([
            "is_checked" => "required|boolean",
            "id" => "nullable|integer|exists:pajak_keluaran_details,id",
            "invoice" => "nullable|string|max:255",
            "select_all" => "nullable|boolean",
            "ids" => "nullable|array|min:1",
            "ids.*" => "integer|distinct|exists:pajak_keluaran_details,id",
            "invoices" => "nullable|array|min:1",
            "invoices.*" => "string|max:255",
        ]);

        try {
            $targetModes = [
                !is_null($validated["id"] ?? null),
                !empty($validated["invoice"] ?? null),
                (bool) ($validated["select_all"] ?? false),
                !empty($validated["ids"] ?? []),
                !empty($validated["invoices"] ?? []),
            ];

            if (
                array_sum(
                    array_map(fn($mode) => $mode ? 1 : 0, $targetModes),
                ) !== 1
            ) {
                return response()->json(
                    [
                        "status" => false,
                        "message" =>
                            "Payload tidak valid. Pilih tepat satu mode target update.",
                    ],
                    422,
                );
            }

            $isChecked = (bool) $validated["is_checked"];
            $scopedQuery = $this->buildScopedPajakKeluaranQuery($request);

            // Handle single ID update
            if (!is_null($validated["id"] ?? null)) {
                $item = (clone $scopedQuery)->findOrFail($validated["id"]);
                $item->is_checked = $isChecked;
                $item->save();
            }

            // Handle single invoice update
            elseif (!empty($validated["invoice"] ?? null)) {
                $invoice = $validated["invoice"];
                $updatedRows = (clone $scopedQuery)
                    ->where("no_invoice", $invoice)
                    ->update([
                        "is_checked" => $isChecked,
                    ]);

                if ($updatedRows === 0) {
                    throw ValidationException::withMessages([
                        "invoice" =>
                            "Data invoice tidak ditemukan atau tidak memiliki akses.",
                    ]);
                }
            }

            // Handle bulk select all (filters applied)
            elseif ((bool) ($validated["select_all"] ?? false)) {
                $dbquery = DB::table("pajak_keluaran_details");
                $this->applyFilters($dbquery, $request);
                $dbquery->update(["is_checked" => $isChecked]);
            }
            // Handle bulk IDs update
            elseif (!empty($validated["ids"] ?? [])) {
                $ids = $validated["ids"];

                $matchedIds = (clone $scopedQuery)
                    ->whereIn("id", $ids)
                    ->pluck("id")
                    ->all();
                $missingIds = array_values(array_diff($ids, $matchedIds));

                if (!empty($missingIds)) {
                    throw ValidationException::withMessages([
                        "ids" =>
                            "Sebagian data tidak ditemukan atau tidak memiliki akses.",
                    ]);
                }

                (clone $scopedQuery)->whereIn("id", $ids)->update([
                    "is_checked" => $isChecked,
                ]);
            }
            // Handle bulk invoices update
            elseif (!empty($validated["invoices"] ?? [])) {
                $invoices = array_values(array_unique($validated["invoices"]));

                $matchedInvoices = (clone $scopedQuery)
                    ->whereIn("no_invoice", $invoices)
                    ->pluck("no_invoice")
                    ->unique()
                    ->values()
                    ->all();
                $missingInvoices = array_values(
                    array_diff($invoices, $matchedInvoices),
                );

                if (!empty($missingInvoices)) {
                    throw ValidationException::withMessages([
                        "invoices" =>
                            "Sebagian invoice tidak ditemukan atau tidak memiliki akses.",
                    ]);
                }

                (clone $scopedQuery)->whereIn("no_invoice", $invoices)->update([
                    "is_checked" => $isChecked,
                ]);
            }

            return response()->json([
                "status" => true,
                "message" => "Status updated successfully",
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error("Failed to update checked status", [
                "context" => __METHOD__,
                "exception" => $e,
                "payload" => $request->except(["_token"]),
            ]);

            return response()->json(
                [
                    "status" => false,
                    "message" => "Terjadi kesalahan saat memperbarui status.",
                ],
                500,
            );
        }
    }

    public function count(Request $request)
    {
        if (!Auth::guard("web")->check()) {
            return response()->json(
                [
                    "status" => false,
                    "message" => "Unauthorized",
                    "data" => [],
                ],
                401,
            );
        }

        try {
            $tipe = $request->query("tipe") ?? "all";
            $pt = $this->normalizeFilter($request->pt ?? ["all"]);
            $brand = $this->normalizeFilter($request->brand ?? ["all"]);
            $depo = $this->normalizeFilter($request->depo ?? ["all"]);
            $periode_awal = null;
            $periode_akhir = null;
            if ($request->has("periode") && !empty($request->periode)) {
                $periode_parts = explode(" - ", $request->periode);
                if (count($periode_parts) === 2) {
                    $periode_awal = \Carbon\Carbon::createFromFormat(
                        "d/m/Y",
                        $periode_parts[0],
                    )->format("Y-m-d");
                    $periode_akhir = \Carbon\Carbon::createFromFormat(
                        "d/m/Y",
                        $periode_parts[1],
                    )->format("Y-m-d");
                } else {
                    $periode_awal = \Carbon\Carbon::createFromFormat(
                        "d/m/Y",
                        $request->periode,
                    )->format("Y-m-d");
                    $periode_akhir = \Carbon\Carbon::createFromFormat(
                        "d/m/Y",
                        $request->periode,
                    )->format("Y-m-d");
                }
            }

            // Base query
            $query = PajakKeluaranDetail::query();
            $query->selectRaw('
                ISNULL(SUM(CASE WHEN is_downloaded = 0 AND is_checked = 1 THEN 1 ELSE 0 END), 0) AS ready2download_count,
                ISNULL(SUM(CASE WHEN is_downloaded = 1 AND is_checked = 1 THEN 1 ELSE 0 END), 0) AS downloaded_count
            ');

            // Additional filters
            if (!in_array("all", $pt)) {
                $query->whereIn("company", $pt);
            }
            if (!in_array("all", $brand)) {
                $query->whereIn("brand", $brand);
            }
            $userInfo = getLoggedInUserInfo();
            $userDepos = $userInfo ? $userInfo->depo : ["all"];
            if (!is_array($userDepos)) {
                $userDepos = [$userDepos];
            }

            if ($userInfo && !in_array("all", $userDepos)) {
                $allowedDepos = MasterDepo::whereIn("code", $userDepos)
                    ->get()
                    ->pluck("name")
                    ->toArray();

                if (!in_array("all", $depo)) {
                    $requestedDepos = MasterDepo::whereIn("code", $depo)
                        ->get()
                        ->pluck("name")
                        ->toArray();
                    $validDepos = array_intersect(
                        $requestedDepos,
                        $allowedDepos,
                    );
                    if (!empty($validDepos)) {
                        $query->whereIn("depo", $validDepos);
                    } else {
                        $query->whereRaw("1 = 0");
                    }
                } else {
                    if (!empty($allowedDepos)) {
                        $query->whereIn("depo", $allowedDepos);
                    } else {
                        $query->whereRaw("1 = 0");
                    }
                }
            } elseif (!in_array("all", $depo)) {
                $depoNames = MasterDepo::whereIn("code", $depo)
                    ->get()
                    ->pluck("name")
                    ->toArray();
                if (!empty($depoNames)) {
                    $query->whereIn("depo", $depoNames);
                } else {
                    $query->whereRaw("1 = 0");
                }
            }
            if ($periode_awal && $periode_akhir) {
                $query
                    ->where("tgl_faktur_pajak", ">=", $periode_awal)
                    ->where("tgl_faktur_pajak", "<=", $periode_akhir);
            }
            if ($request->has("chstatus")) {
                switch ($request->chstatus) {
                    case "checked-ready2download":
                        $query->where("is_checked", "1");
                        break;

                    case "unchecked":
                        $query->where("is_checked", "0");
                        break;

                    case "checked-downloaded":
                        $query->where("is_downloaded", "1");
                        break;

                    default:
                        break;
                }
            }

            // Additional conditions based on the type
            $pkp = MasterPkp::where("is_active", true)
                ->pluck("IDPelanggan")
                ->toArray();
            switch ($tipe) {
                case "pkp":
                    $query->whereRaw("
                    (
                        tipe_ppn = 'PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)
                    )
                    OR
                    (has_moved = 'y' AND moved_to = 'pkp')");
                    break;
                case "pkpnppn":
                    $query->whereRaw("
                    (
                        tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)
                    ) OR (has_moved = 'y' AND moved_to = 'pkpnppn')");
                    break;
                case "npkp":
                    $query->whereRaw("
                    (
                        tipe_ppn = 'PPN' AND (hargatotal_sblm_ppn > 0 OR hargatotal_sblm_ppn <= -1000000) AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)
                    ) OR (has_moved = 'y' AND moved_to = 'npkp')");
                    break;
                case "npkpnppn":
                    $query->whereRaw("
                    (
                        tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)
                    ) OR (has_moved = 'y' AND moved_to = 'npkpnppn')");
                    break;
                case "retur":
                    $query->whereRaw(
                        "qty_pcs < 0 AND hargatotal_sblm_ppn >= -1000000 AND has_moved = 'n' OR moved_to = 'retur'",
                    );
                    break;
                case "nonstandar":
                    $this->applyNonStandarScope($query);
                    break;
            }
            // Log::info('sql count: '.$query->toSql());
            $counts = $query->get();

            return response()->json(
                [
                    "status" => true,
                    "message" => "Counts retrieved successfully",
                    "data" => $counts,
                ],
                200,
            );
        } catch (\Throwable $th) {
            Log::error("Failed to count pajak keluaran data", [
                "context" => __METHOD__,
                "exception" => $th,
            ]);

            return response()->json(
                [
                    "status" => false,
                    "error" => "Terjadi kesalahan saat menghitung data.",
                ],
                500,
            );
        }
    }

    public function download(Request $request)
    {
        try {
            $request->validate([
                "tipe" =>
                    "nullable|in:pkp,pkpnppn,npkp,npkpnppn,retur,nonstandar,all",
                "chstatus" =>
                    "nullable|in:checked-ready2download,checked-downloaded,unchecked,all",
                "periode" => [
                    "nullable",
                    'regex:/^\d{2}\/\d{2}\/\d{4}( - \d{2}\/\d{2}\/\d{4})?$/',
                ],
            ]);
            if (
                !Auth::user()->canAccessMenu(
                    "reguler-pajak-keluaran",
                    AccessGroup::LEVEL_READ_WRITE,
                )
            ) {
                abort(403, "Unauthorized action.");
            }
            $tipe = $request->query("tipe") ?? "all";
            $pt = $request->query("pt", []);
            $brand = $request->query("brand", []);
            $depo = $request->query("depo", []);
            $periode = $request->query("periode");
            $chstatus = $request->query("chstatus", "checked-ready2download");
            $headers = [
                "Content-Type" =>
                    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                "Content-Disposition" =>
                    'attachment; filename="pajak_keluaran_' . $tipe . '.xlsx"',
            ];
            $writerType = "Xlsx";

            return Excel::download(
                new PajakKeluaranDetailExport(
                    $tipe,
                    $pt,
                    $brand,
                    $depo,
                    $periode,
                    $chstatus,
                ),
                "pajak_keluaran_" . $tipe . ".xlsx",
                $writerType,
                $headers,
            );
        } catch (\Throwable $th) {
            Log::error("Failed to download pajak keluaran", [
                "context" => __METHOD__,
                "exception" => $th,
            ]);

            return response()->json(
                [
                    "status" => false,
                    "message" => "Terjadi kesalahan saat mengunduh data.",
                ],
                500,
            );
        }
    }

    public function downloadDb(Request $request)
    {
        try {
            $request->validate([
                "tipe" =>
                    "nullable|in:pkp,pkpnppn,npkp,npkpnppn,retur,nonstandar,all",
                "chstatus" =>
                    "nullable|in:checked-ready2download,checked-downloaded,unchecked,all",
                "periode" => [
                    "nullable",
                    'regex:/^\d{2}\/\d{2}\/\d{4}( - \d{2}\/\d{2}\/\d{4})?$/',
                ],
            ]);
            if (
                !Auth::user()->canAccessMenu(
                    "reguler-pajak-keluaran",
                    AccessGroup::LEVEL_READ_WRITE,
                )
            ) {
                abort(403, "Unauthorized action.");
            }
            $tipe = $request->query("tipe") ?? "all";
            $pt = $request->query("pt", []);
            $brand = $request->query("brand", []);
            $depo = $request->query("depo", []);
            $periode = $request->query("periode");
            $chstatus = $request->query("chstatus", "checked-ready2download");
            $headers = [
                "Content-Type" =>
                    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                "Content-Disposition" =>
                    'attachment; filename="pajak_keluaran_' . $tipe . '.xlsx"',
            ];

            return Excel::download(
                new PajakKeluaranTemplateExport(
                    $tipe,
                    $pt,
                    $brand,
                    $depo,
                    $periode,
                    $chstatus,
                ),
                "pajak_keluaran_" . $tipe . ".xlsx",
                "Xlsx",
                $headers,
            );
        } catch (\Throwable $th) {
            Log::error("Failed to download pajak keluaran db", [
                "context" => __METHOD__,
                "exception" => $th,
            ]);

            return response()->json(
                [
                    "status" => false,
                    "message" => "Terjadi kesalahan saat mengunduh data DB.",
                ],
                500,
            );
        }
    }

    public function getAvailableDates(Request $request)
    {
        try {
            // Build query to get distinct dates
            $query = DB::table("pajak_keluaran_details")
                ->select(
                    DB::raw(
                        "DISTINCT CAST(tgl_faktur_pajak AS DATE) as tanggal",
                    ),
                )
                ->whereNotNull("tgl_faktur_pajak");

            // Apply filters
            $pt = $this->normalizeFilter($request->pt ?? ["all"]);
            $brand = $this->normalizeFilter($request->brand ?? ["all"]);
            $depo = $this->normalizeFilter($request->depo ?? ["all"]);

            if ($request->has("pt") && !in_array("all", $pt)) {
                $query->whereIn("company", $pt);
            }

            if ($request->has("brand") && !in_array("all", $brand)) {
                $query->whereIn("brand", $brand);
            }

            $userInfo = getLoggedInUserInfo();
            $userDepos = $userInfo ? $userInfo->depo : ["all"];
            if (!is_array($userDepos)) {
                $userDepos = [$userDepos];
            }

            if ($userInfo && !in_array("all", $userDepos)) {
                $allowedDepos = MasterDepo::whereIn("code", $userDepos)
                    ->get()
                    ->pluck("name")
                    ->toArray();

                if ($request->has("depo") && !in_array("all", $depo)) {
                    $requestedDepos = MasterDepo::whereIn("code", $depo)
                        ->get()
                        ->pluck("name")
                        ->toArray();
                    $validDepos = array_intersect(
                        $requestedDepos,
                        $allowedDepos,
                    );
                    if (!empty($validDepos)) {
                        $query->whereIn("depo", $validDepos);
                    } else {
                        $query->whereRaw("1 = 0");
                    }
                } else {
                    if (!empty($allowedDepos)) {
                        $query->whereIn("depo", $allowedDepos);
                    } else {
                        $query->whereRaw("1 = 0");
                    }
                }
            } elseif ($request->has("depo") && !in_array("all", $depo)) {
                $depos = MasterDepo::whereIn("code", $depo)
                    ->get()
                    ->pluck("name")
                    ->toArray();
                if (!empty($depos)) {
                    $query->whereIn("depo", $depos);
                } else {
                    $query->whereRaw("1 = 0");
                }
            }

            // Get dates and format them
            $dates = $query->orderBy("tanggal", "asc")->get();
            $formattedDates = $dates
                ->map(function ($item) {
                    return \Carbon\Carbon::parse($item->tanggal)->format(
                        "Y-m-d",
                    );
                })
                ->toArray();

            return response()->json([
                "status" => true,
                "data" => $formattedDates,
            ]);
        } catch (\Throwable $th) {
            Log::error("Failed to get available pajak keluaran dates", [
                "context" => __METHOD__,
                "exception" => $th,
            ]);

            return response()->json(
                [
                    "status" => false,
                    "message" =>
                        "Terjadi kesalahan saat mengambil tanggal tersedia.",
                    "data" => [],
                ],
                500,
            );
        }
    }

    public function uploadPMCoretax(Request $request)
    {
        try {
            $request->validate([
                "file" => "required|file|mimes:csv,txt,xlsx,xls",
            ]);

            $file = $request->file("file");
            $path = $file->store("public/import");

            // Create import instance
            $import = new PajakMasukanCoretaxImport();

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
                "Import Pajak Masukan Coretax",
                "Import Pajak Masukan Coretax - Inserted: {$insertedCount}, Updated: {$updatedCount}, Errors: {$errorCount}",
                "-",
                "pajak_masukan_coretax",
                "info",
                $request,
            );

            // Create success message with details
            $message = "Data berhasil diimport! ";
            $message .= "Berhasil insert: {$insertedCount}, ";
            $message .= "Duplikat (diupdate): {$updatedCount}, ";
            $message .= "Error: {$errorCount}, ";
            $message .= "Total diproses: {$totalProcessed}";

            $responseData = [
                "success" => $message,
                "stats" => [
                    "inserted" => $insertedCount,
                    "updated" => $updatedCount,
                    "errors" => $errorCount,
                    "total" => $totalProcessed,
                ],
            ];

            if ($errorCount > 0) {
                $responseData["error_messages"] = $errorMessages;
            }

            return redirect()->back()->with($responseData);
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with("error", "Import gagal: " . $th->getMessage());
        }
    }

    private function applyFilters($dbquery, Request $request)
    {
        $metadata = [
            "periode_awal" => null,
            "periode_akhir" => null,
            "tipe" => "",
            "chstatus" => "",
        ];

        $searchValue = $request->get("search")["value"] ?? "";

        $allowedColumns = [
            "customer_id",
            "nik",
            "nama_customer_sistem",
            "npwp_customer",
            "no_do",
            "no_invoice",
            "kode_produk",
            "nama_produk",
            "satuan",
            "qty_pcs",
            "hargasatuan_sblm_ppn",
            "hargatotal_sblm_ppn",
            "disc",
            "dpp",
            "dpp_lain",
            "ppn",
            "tgl_faktur_pajak",
            "alamat_sistem",
            "tipe_ppn",
            "nama_sesuai_npwp",
            "alamat_npwp_lengkap",
            "no_telepon",
            "no_fp",
            "brand",
            "depo",
            "area",
            "tipe_jual",
            "kode_jenis_fp",
            "status_fp",
            "id_tku_pembeli",
            "jenis",
            "barang_jasa",
        ];

        // Column specific filters
        if ($request->has("columns")) {
            foreach ($request->get("columns") as $column) {
                $columnName = $column["data"] ?? null;
                if (
                    $columnName === "nonstandar_keterangan" &&
                    ($request->tipe ?? null) === "nonstandar" &&
                    !empty($column["search"]["value"])
                ) {
                    $this->applyNonStandarReasonFilter(
                        $dbquery,
                        $column["search"]["value"],
                    );

                    continue;
                }
                if (
                    $columnName &&
                    in_array($columnName, $allowedColumns, true) &&
                    !empty($column["search"]["value"])
                ) {
                    $dbquery->where(
                        $columnName,
                        "like",
                        "%{$column["search"]["value"]}%",
                    );
                }
            }
        }

        // Filtering by search value
        if (!empty($searchValue)) {
            $dbquery->where(function ($q) use ($searchValue) {
                $q->where("no_invoice", "like", "%{$searchValue}%")
                    ->orWhere("no_do", "like", "%{$searchValue}%")
                    ->orWhere("kode_produk", "like", "%{$searchValue}%")
                    ->orWhere("nama_produk", "like", "%{$searchValue}%")
                    ->orWhere("brand", "like", "%{$searchValue}%")
                    ->orWhere("depo", "like", "%{$searchValue}%")
                    ->orWhere("customer_id", "like", "%{$searchValue}%")
                    ->orWhere("nik", "like", "%{$searchValue}%")
                    ->orWhere(
                        "nama_customer_sistem",
                        "like",
                        "%{$searchValue}%",
                    );
            });
        }
        // Additional filters
        $pt = $this->normalizeFilter($request->pt ?? ["all"]);
        $brand = $this->normalizeFilter($request->brand ?? ["all"]);
        $depo = $this->normalizeFilter($request->depo ?? ["all"]);

        if ($request->has("pt") && !in_array("all", $pt)) {
            $dbquery->whereIn("company", $pt);
        }
        if ($request->has("brand") && !in_array("all", $brand)) {
            $dbquery->whereIn("brand", $brand);
        }
        if ($request->has("depo") && !in_array("all", $depo)) {
            $userInfo = getLoggedInUserInfo();

            // If user has specific depo access, intersect requested depos with allowed depos
            if ($userInfo && !in_array("all", $userInfo->depo)) {
                // Filter requested depos that user actually has access to
                $allowedDepos = MasterDepo::whereIn("code", $userInfo->depo)
                    ->get()
                    ->pluck("name")
                    ->toArray();

                // Get names of requested depos
                $requestedDepos = MasterDepo::whereIn("code", $depo)
                    ->get()
                    ->pluck("name")
                    ->toArray();

                // Intersect to ensure user only accesses allowed depos
                $validDepos = array_intersect($requestedDepos, $allowedDepos);

                if (!empty($validDepos)) {
                    $dbquery->whereIn("depo", $validDepos);
                } else {
                    // If intersection is empty (user requesting access to unauthorized depos), return no results
                    $dbquery->whereRaw("1 = 0");
                }
            } else {
                // User has 'all' access, so just use requested depos
                $depos = MasterDepo::whereIn("code", $depo)
                    ->get()
                    ->pluck("name")
                    ->toArray();
                $dbquery->whereIn("depo", $depos);
            }
        } elseif ($request->has("depo") && in_array("all", $depo)) {
            // Logic for 'all' selection
            $userInfo = getLoggedInUserInfo();
            if ($userInfo && !in_array("all", $userInfo->depo)) {
                $depo = MasterDepo::whereIn("code", $userInfo->depo)
                    ->get()
                    ->pluck("name")
                    ->toArray();
                $dbquery->whereIn("depo", $depo);
            }
        } else {
            $userInfo = getLoggedInUserInfo();
            if ($userInfo && !in_array("all", $userInfo->depo)) {
                $allowedDepo = MasterDepo::whereIn("code", $userInfo->depo)
                    ->get()
                    ->pluck("name")
                    ->toArray();
                if (!empty($allowedDepo)) {
                    $dbquery->whereIn("depo", $allowedDepo);
                } else {
                    $dbquery->whereRaw("1 = 0");
                }
            }
        }
        if ($request->has("periode") && !empty($request->periode)) {
            $periode = explode(" - ", $request->periode);
            if (count($periode) === 2) {
                $metadata["periode_awal"] = \Carbon\Carbon::createFromFormat(
                    "d/m/Y",
                    $periode[0],
                )->format("Y-m-d");
                $metadata["periode_akhir"] = \Carbon\Carbon::createFromFormat(
                    "d/m/Y",
                    $periode[1],
                )->format("Y-m-d");
                $dbquery->whereBetween("tgl_faktur_pajak", [
                    $metadata["periode_awal"],
                    $metadata["periode_akhir"],
                ]);
            } else {
                $metadata["periode_awal"] = \Carbon\Carbon::createFromFormat(
                    "d/m/Y",
                    $request->periode,
                )->format("Y-m-d");
                $metadata["periode_akhir"] = \Carbon\Carbon::createFromFormat(
                    "d/m/Y",
                    $request->periode,
                )->format("Y-m-d");
                $dbquery->whereBetween("tgl_faktur_pajak", [
                    $metadata["periode_awal"],
                    $metadata["periode_akhir"],
                ]);
            }
        }
        if ($request->has("chstatus")) {
            switch ($request->chstatus) {
                case "checked-ready2download":
                    $dbquery->where("is_checked", 1);
                    $metadata["chstatus"] = " AND is_checked = 1";
                    break;

                case "unchecked":
                    $dbquery->where("is_checked", 0);
                    $metadata["chstatus"] = " AND is_checked = 0";
                    break;

                case "checked-downloaded":
                    $dbquery->where("is_checked", 1);
                    $dbquery->where("is_downloaded", 1);
                    $metadata["chstatus"] =
                        " AND is_checked = 1 AND is_downloaded = 1";
                    break;

                default:
                    break;
            }
        }
        if ($request->has("tipe")) {
            $pkp = MasterPkp::where("is_active", true)
                ->pluck("IDPelanggan")
                ->toArray();
            if ($request->tipe == "pkp") {
                $dbquery->whereRaw("
                (
                    tipe_ppn = 'PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)
                )
                OR
                (has_moved = 'y' AND moved_to = 'pkp')");
                $metadata["tipe"] =
                    " AND e.szTaxTypeId = 'PPN' AND a.szCustId IN ('" .
                    implode("','", $pkp) .
                    "')";
            }
            if ($request->tipe == "pkpnppn") {
                $dbquery->whereRaw("
                (
                    tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)
                ) OR (has_moved = 'y' AND moved_to = 'pkpnppn')");
                $metadata["tipe"] =
                    " AND e.szTaxTypeId = 'NON-PPN' AND a.szCustId IN ('" .
                    implode("','", $pkp) .
                    "')";
            }
            if ($request->tipe == "npkp") {
                $dbquery->whereRaw("
                (
                    tipe_ppn = 'PPN' AND (hargatotal_sblm_ppn > 0 OR hargatotal_sblm_ppn <= -1000000) AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)
                ) OR (has_moved = 'y' AND moved_to = 'npkp')");
                $metadata["tipe"] =
                    " AND e.szTaxTypeId = 'PPN' AND a.szCustId NOT IN ('" .
                    implode("','", $pkp) .
                    "')";
            }
            if ($request->tipe == "npkpnppn") {
                $dbquery->whereRaw("
                (
                    tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)
                ) OR (has_moved = 'y' AND moved_to = 'npkpnppn')");
                $metadata["tipe"] =
                    " AND e.szTaxTypeId = 'NON-PPN' AND a.szCustId NOT IN ('" .
                    implode("','", $pkp) .
                    "')";
            }
            if ($request->tipe == "retur") {
                $dbquery->whereRaw(
                    "qty_pcs < 0 AND hargatotal_sblm_ppn >= -1000000 AND has_moved = 'n' OR moved_to = 'retur'",
                );
            }
            if ($request->tipe == "nonstandar") {
                $this->applyNonStandarScope($dbquery);
            }
        }

        return $metadata;
    }

    private function buildNonStandarReason(
        ?string $nik,
        ?string $hasMoved,
        ?string $movedTo,
        ?string $tipePpn = null,
        $qtyPcs = null,
        $hargaTotalSblmPpn = null,
        ?string $customerId = null,
    ): string {
        if ($hasMoved === "y" && $movedTo === "nonstandar") {
            return "Dipindahkan manual ke tab Non Standar";
        }

        $nikDigits = preg_replace("/\D+/", "", (string) $nik);
        $nikLength = strlen($nikDigits);
        $nikLastTwoDigits = $nikLength >= 2 ? substr($nikDigits, -2) : "";

        if ($nikLength !== 16 && $nikLastTwoDigits === "00") {
            return "Jumlah digit NIK tidak standar dan 2 digit akhir NIK 00";
        }

        if ($nikLength !== 16) {
            return "Jumlah digit NIK tidak standar";
        }

        if ($nikLastTwoDigits === "00") {
            return "2 digit akhir NIK 00";
        }

        if (
            $this->isNonStandarFallback(
                $hasMoved,
                $tipePpn,
                $qtyPcs,
                $hargaTotalSblmPpn,
                $customerId,
            )
        ) {
            return "Tidak memenuhi kategori standar (PKP/PKP NON PPN/NON PKP/NON PKP NON PPN/RETUR)";
        }

        return "Masuk kriteria Non Standar";
    }

    private function applyNonStandarScope($query): void
    {
        $query->whereRaw(
            "(has_moved = 'y' AND moved_to = 'nonstandar') OR (has_moved = 'n' AND ({$this->normalizedNikLengthSql()} != 16 OR {$this->normalizedNikLastTwoSql()} = '00')) OR " .
                $this->nonStandarFallbackConditionSql(),
        );
    }

    private function nonStandarFallbackConditionSql(): string
    {
        return "(has_moved = 'n' AND NOT ((tipe_ppn = 'PPN' AND qty_pcs > 0 AND customer_id IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)) OR (tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND customer_id IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)) OR (tipe_ppn = 'PPN' AND (hargatotal_sblm_ppn > 0 OR hargatotal_sblm_ppn <= -1000000) AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)) OR (tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)) OR (qty_pcs < 0 AND hargatotal_sblm_ppn >= -1000000)))";
    }

    private function applyNonStandarReasonFilter(
        $dbquery,
        string $keyword,
    ): void {
        $normalizedKeyword = mb_strtolower(trim($keyword));

        $dbquery->where(function ($query) use ($normalizedKeyword) {
            $applied = false;

            if (
                str_contains($normalizedKeyword, "manual") ||
                str_contains($normalizedKeyword, "pindah") ||
                str_contains($normalizedKeyword, "dipindahkan")
            ) {
                $query->orWhere(function ($manualQuery) {
                    $manualQuery
                        ->where("has_moved", "y")
                        ->where("moved_to", "nonstandar");
                });
                $applied = true;
            }

            if (
                str_contains($normalizedKeyword, "nik") ||
                str_contains($normalizedKeyword, "tidak standar") ||
                str_contains($normalizedKeyword, "00")
            ) {
                $query->orWhereRaw(
                    "(has_moved = 'n' AND ({$this->normalizedNikLengthSql()} != 16 OR {$this->normalizedNikLastTwoSql()} = '00'))",
                );
                $applied = true;
            }

            if (
                str_contains($normalizedKeyword, "fallback") ||
                str_contains($normalizedKeyword, "tidak memenuhi") ||
                str_contains($normalizedKeyword, "kategori") ||
                str_contains($normalizedKeyword, "standar")
            ) {
                $query->orWhereRaw($this->nonStandarFallbackConditionSql());
                $applied = true;
            }

            if (!$applied) {
                $query->whereRaw("1 = 0");
            }
        });
    }

    private function isNonStandarFallback(
        ?string $hasMoved,
        ?string $tipePpn,
        $qtyPcs,
        $hargaTotalSblmPpn,
        ?string $customerId,
    ): bool {
        if ($hasMoved !== "n") {
            return false;
        }

        static $activePkpCustomers = null;

        if ($activePkpCustomers === null) {
            $activePkpCustomers = MasterPkp::where("is_active", true)
                ->pluck("IDPelanggan")
                ->toArray();
        }

        $isPkp = in_array($customerId, $activePkpCustomers, true);
        $qty = floatval($qtyPcs ?? 0);
        $hargaTotal = floatval($hargaTotalSblmPpn ?? 0);

        $isPkpPpn = $tipePpn === "PPN" && $qty > 0 && $isPkp;
        $isPkpNonPpn = $tipePpn === "NON-PPN" && $qty > 0 && $isPkp;
        $isNonPkp =
            $tipePpn === "PPN" &&
            ($hargaTotal > 0 || $hargaTotal <= -1000000) &&
            !$isPkp;
        $isNonPkpNonPpn = $tipePpn === "NON-PPN" && $qty > 0 && !$isPkp;
        $isRetur = $qty < 0 && $hargaTotal >= -1000000;

        return !(
            $isPkpPpn ||
            $isPkpNonPpn ||
            $isNonPkp ||
            $isNonPkpNonPpn ||
            $isRetur
        );
    }

    private function hasWriteAccess(): bool
    {
        return Auth::user()->canAccessMenu(
            "reguler-pajak-keluaran",
            AccessGroup::LEVEL_READ_WRITE,
        );
    }

    private function buildScopedPajakKeluaranQuery(Request $request)
    {
        $query = PajakKeluaranDetail::query();

        $userInfo = getLoggedInUserInfo();
        $userDepos = $userInfo ? Arr::wrap($userInfo->depo) : ["all"];

        if (!in_array("all", $userDepos, true)) {
            $allowedDepoNames = MasterDepo::whereIn("code", $userDepos)
                ->pluck("name")
                ->toArray();

            if (empty($allowedDepoNames)) {
                $query->whereRaw("1 = 0");
            } else {
                $query->whereIn("depo", $allowedDepoNames);
            }
        }

        return $query;
    }

    private function normalizedNikSql(): string
    {
        return "REPLACE(REPLACE(LTRIM(RTRIM(ISNULL(nik, ''))), '-', ''), ' ', '')";
    }

    private function normalizedNikLengthSql(): string
    {
        return "LEN(" . $this->normalizedNikSql() . ")";
    }

    private function normalizedNikLastTwoSql(): string
    {
        return "RIGHT(" . $this->normalizedNikSql() . ", 2)";
    }

    private function resolveSortableColumn(?string $columnName): string
    {
        $allowedColumns = [
            "customer_id",
            "nik",
            "nama_customer_sistem",
            "npwp_customer",
            "no_do",
            "no_invoice",
            "kode_produk",
            "nama_produk",
            "satuan",
            "qty_pcs",
            "hargasatuan_sblm_ppn",
            "hargatotal_sblm_ppn",
            "disc",
            "dpp",
            "dpp_lain",
            "ppn",
            "tgl_faktur_pajak",
            "alamat_sistem",
            "tipe_ppn",
            "type_pajak",
            "nama_sesuai_npwp",
            "alamat_npwp_lengkap",
            "no_telepon",
            "no_fp",
            "brand",
            "depo",
            "area",
            "tipe_jual",
            "type_jual",
            "kode_jenis_fp",
            "fp_normal_pengganti",
            "id_tku_pembeli",
            "barang_jasa",
            "is_checked",
            "is_downloaded",
            "has_moved",
            "moved_to",
        ];

        if (
            !is_string($columnName) ||
            !in_array($columnName, $allowedColumns, true)
        ) {
            return "tgl_faktur_pajak";
        }

        if ($columnName === "type_pajak") {
            return "tipe_ppn";
        }

        if ($columnName === "type_jual") {
            return "tipe_jual";
        }

        return $columnName;
    }

    private function normalizeFilter($value): array
    {
        $value = array_values(
            array_filter(
                array_map(function ($item) {
                    return is_string($item) ? trim($item) : $item;
                }, Arr::wrap($value)),
                function ($item) {
                    return $item !== null && $item !== "";
                },
            ),
        );

        if (in_array("all", $value, true)) {
            return ["all"];
        }

        return $value === [] ? ["all"] : $value;
    }
}
