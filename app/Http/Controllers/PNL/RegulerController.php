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
        try {
            $draw = $request->get("draw");
            $start = $request->get("start");
            $rowperpage = $request->get("length");
            $columnIndex = $request->get("order")[0]["column"] ?? 0;
            $columnName =
                $request->get("columns")[$columnIndex]["data"] ?? "created_at";
            $columnSortOrder = $request->get("order")[0]["dir"] ?? "desc";
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
                ->orderBy($columnName)
                ->skip($start)
                ->take($rowperpage)
                ->get();

            return response()->json([
                "draw" => intval($draw),
                "iTotalRecords" => $totalRecords,
                "iTotalDisplayRecords" => $totalRecordswithFilter,
                "aaData" => $records,
                "status" => true,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "data" => [],
            ]);
        }
    }

    public function dtPKDbGetData(Request $request)
    {
        try {
            $draw = $request->get("draw");
            $start = $request->get("start");
            $rowperpage = $request->get("length");
            $columnIndex = $request->get("order")[0]["column"] ?? 0;
            $columnName =
                $request->get("columns")[$columnIndex]["data"] ?? "created_at";
            $columnSortOrder = $request->get("order")[0]["dir"] ?? "desc";
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
            }

            return response()->json([
                "draw" => intval($draw),
                "iTotalRecords" => $totalRecords,
                "iTotalDisplayRecords" => $totalRecordswithFilter,
                "aaData" => $records,
                "status" => true,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "data" => [],
            ]);
        }
    }

    public function updateMove2(Request $request)
    {
        try {
            $request->validate([
                "ids" => "required",
                "move_from" =>
                    "required|in:pkp,pkpnppn,npkp,npkpnppn,retur,nonstandar",
                "move_to" =>
                    "required|in:pkp,pkpnppn,npkp,npkpnppn,retur,nonstandar",
            ]);
            $ids = $request->input("ids");
            $items = PajakKeluaranDetail::whereIn("id", $ids)->get();
            foreach ($items as $item) {
                $item->has_moved = "y";
                $item->moved_to = $request->input("move_to");
                $item->moved_at = now();
                /** @var \App\Models\PajakKeluaranDetail $item */
                $item->save();
                LogController::createLog(
                    Auth::user()->id,
                    "Move Item from " .
                        $request->input("move_from") .
                        " to " .
                        $request->input("move_to"),
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
                        $request->input("move_from") .
                        ", move_to: " .
                        $request->input("move_to") .
                        "}",
                    "pajak_keluaran_details",
                    "info",
                    $request,
                );
            }

            return response()->json([
                "status" => true,
                "message" => "Item moved successfully",
                "data" => $item,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "data" => [],
            ]);
        }
    }

    public function updateChecked(Request $request)
    {
        try {
            $isChecked = $request->input("is_checked");

            // Handle single ID update
            if ($request->has("id")) {
                $id = $request->input("id");
                $item = PajakKeluaranDetail::findOrFail($id);
                $item->is_checked = $isChecked;
                $item->save();
            }

            // Handle single invoice update
            if ($request->has("invoice")) {
                $invoice = $request->input("invoice");
                PajakKeluaranDetail::where("no_invoice", $invoice)->update([
                    "is_checked" => $isChecked,
                ]);
            }

            // Handle bulk select all (filters applied)
            if ($request->has("select_all") && $request->select_all == 1) {
                $dbquery = DB::table("pajak_keluaran_details");
                $this->applyFilters($dbquery, $request);
                $dbquery->update(["is_checked" => $isChecked]);
            }
            // Handle bulk IDs update
            elseif ($request->has("ids")) {
                $ids = $request->input("ids");
                PajakKeluaranDetail::whereIn("id", $ids)->update([
                    "is_checked" => $isChecked,
                ]);
            }
            // Handle bulk invoices update
            elseif ($request->has("invoices")) {
                $invoices = $request->input("invoices");
                PajakKeluaranDetail::whereIn("no_invoice", $invoices)->update([
                    "is_checked" => $isChecked,
                ]);
            }

            return response()->json([
                "status" => true,
                "message" => "Status updated successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "status" => false,
                    "message" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function count(Request $request)
    {
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
                    $query->whereRaw(
                        "(has_moved = 'n' AND LEN(nik) IN (15, 16) AND RIGHT(nik, 2) != '00') OR (has_moved = 'y' AND moved_to = 'nonstandar')",
                    );
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
            return response()->json(
                [
                    "status" => false,
                    "error" => $th->getMessage(),
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
            return response()->json(
                [
                    "status" => false,
                    "message" => $th->getMessage(),
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
            return response()->json(
                [
                    "status" => false,
                    "message" => $th->getMessage(),
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
            return response()->json(
                [
                    "status" => false,
                    "message" => $th->getMessage(),
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
                $dbquery->whereRaw(
                    "(has_moved = 'n' AND LEN(nik) IN (15, 16) AND RIGHT(nik, 2) != '00') OR (has_moved = 'y' AND moved_to = 'nonstandar')",
                );
            }
        }

        return $metadata;
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
