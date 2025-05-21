<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PajakKeluaranDetail extends Model
{
    protected $table = 'pajak_keluaran_details';

    protected $fillable = [
        'no_invoice',
        'no_do',
        'kode_produk',
        'qty_pcs',
        'hargasatuan_sblm_ppn',
        'disc',
        'hargatotal_sblm_ppn',
        'dpp',
        'ppn',
        'tgl_faktur_pajak',
        'depo',
        'area',
        'nama_produk',
        'npwp_customer',
        'customer_id',
        'nama_customer_sistem',
        'alamat_sistem',
        'type_pajak',
        'satuan',
        'nama_sesuai_npwp',
        'alamat_npwp_lengkap',
        'no_telepon',
        'no_fp',
        'brand',
        'type_jual',
        'kode_jenis_fp',
        'fp_normal_pengganti',
        'nik',
        'dpp_lain',
        'id_tku_pembeli',
        'barang_jasa',
        'tipe_ppn',
        'company',
        'is_checked',
        'is_downloaded'
    ];

    protected $casts = [
        'hargasatuan_sblm_ppn' => 'decimal:4',
        'disc' => 'decimal:4',
        'hargatotal_sblm_ppn' => 'decimal:4',
        'dpp' => 'decimal:4',
        'ppn' => 'decimal:4',
        'tgl_faktur_pajak' => 'date:d-m-Y',
        'dpp_lain' => 'decimal:4',
        'nik' => 'string'
    ];

    public static function getFromLive($pt, $brand, $depo, $start, $end, $tipe, $chstatus)
    {
        try {
            $filter_pt = $pt !== 'all' ? " AND e.szCategory_9 = '$pt'" : "";
            $filter_brand = $brand !== 'all' ? " AND e.szCategory_1 = '$brand'" : "";
            $filter_tanggal = " FORMAT (a.dtmDelivery, 'yyyy-MM-dd') BETWEEN '$start' AND '$end'";
            $filter_tipe = $tipe ?? '';
            if ($depo == 'all') {
                $currentUserDepo = Auth::user()->depo;
                if (str_contains($currentUserDepo, '|')) {
                    $currentUserDepo = explode("|", $currentUserDepo);
                    if (in_array('all', $currentUserDepo)) {
                        $filter_depo = "";
                    } else {
                        $filter_depo = " AND a.[szWorkplaceId] IN (" . implode(',', $currentUserDepo) . ")";
                    }
                } else {
                    $filter_depo = "";
                }
            } else {
                $filter_depo = " AND a.[szWorkplaceId] = '$depo'";
            }

            $query  = "SELECT
                    a.[szFInvoiceId] AS 'no_invoice',
                    a.[szDoId] AS 'no_do',
                    b.szProductId AS 'kode_produk',
                    b.decUomQty AS 'qty_pcs',
                    b.decPrice/ 1.11 AS 'hargasatuan_sblm_ppn',
                    (
                        SELECT
                            isnull(SUM(c.decBonusAmount), 0) diskon
                        FROM
                            BOS_SD_FDoItemBonusSource c
                        WHERE
                        c.szfDoId= a.szDoId
                            AND c.szProductId= b.szProductId
                            AND c.szOrderItemTypeId= b.szOrderItemTypeId
                    ) / 1.11 AS 'disc',
                    b.decDpp AS 'hargatotal_sblm_ppn',
                    b.decDpp - (
                        SELECT
                            isnull(SUM(c.decBonusAmount), 0) diskon
                        FROM
                            BOS_SD_FDoItemBonusSource c
                        WHERE
                        c.szfDoId= a.szDoId
                            AND c.szProductId= b.szProductId
                            AND c.szOrderItemTypeId= b.szOrderItemTypeId
                    ) / 1.11 AS 'dpp',
                    (
                        b.decDpp- (
                        SELECT
                            isnull(SUM(c.decBonusAmount), 0) diskon
                        FROM
                            BOS_SD_FDoItemBonusSource c
                        WHERE
                            c.szfDoId= a.szDoId
                            AND c.szProductId= b.szProductId
                            AND c.szOrderItemTypeId= b.szOrderItemTypeId) / 1.11
                    ) * 11 / 100 AS 'ppn',
                    format (a.[dtmDelivery], 'MM-dd-yyyy') AS 'tgl_faktur_pajak',
                    f.[szName] AS 'depo',
                    area = '',
                    e.[szName] AS 'nama_produk',
                    tax.[szNPWP] AS 'npwp_customer',
                    a.[szCustId] AS 'customer_id',
                    ltrim(
                    rtrim(
                    replace(
                        replace(replace(replace(d.[szName], CHAR(13), ''), CHAR(10), ''), CHAR(9), ''),
                        CHAR(11),
                    ''))) AS 'nama_customer_sistem',
                    ltrim(
                    rtrim(
                    replace(
                        replace(replace(replace(a.[DeliveryszAddress_1], CHAR(13), ''), CHAR(10), ''), CHAR(9), ''),
                        CHAR(11),
                    ''))) AS 'alamat_sistem',
                    type_pajak = 0,
                    satuan = 'PCS',
                    taxinv.[szCustTaxNm] AS 'nama_sesuai_npwp',
                    concat (taxinv.[TaxszAddress_1], ', ', taxinv.[TaxszDistrict], ', ', taxinv.[TaxszAddress_2], ', ', taxinv.[TaxszCity], ', ', taxinv.[TaxszZipCode]) AS 'alamat_npwp_lengkap',
                    taxinv.[TaxszPhoneNo_1] AS 'no_telepon',
                    0 AS 'no_fp',
                    d.[szCategory_1] AS 'brand',
                    CASE
                        WHEN a.bcash = 0 THEN 'CREDIT'
                        ELSE 'CASH'
                    END AS 'type_jual',
                    0 AS 'kode_jenis_fp',
                    0 AS 'fp_normal_pengganti',
                    tax.[szNoKTP] AS 'nik',
                    (
                        b.decDpp- (
                        SELECT
                            isnull(SUM(c.decBonusAmount), 0) diskon
                        FROM
                            BOS_SD_FDoItemBonusSource c
                        WHERE
                            c.szfDoId= a.szDoId
                            AND c.szProductId= b.szProductId
                            AND c.szOrderItemTypeId= b.szOrderItemTypeId) / 1.11
                    ) * 11 / 12 AS 'dpp_lain',
                    concat (tax.[szNoKTP], '000000') AS 'id_tku_pembeli',
                    'barang_jasa' AS 'barang_jasa',
                    0 AS is_checked,
                    0 AS is_downloaded,
                    e.szTaxTypeId AS tipe_ppn,
                    e.szCategory_9 AS company
                FROM
                    [padma_live].[dbo].[BOS_SD_FDo] a
                    LEFT JOIN BOS_SD_FDoItem b ON a.[szDoId] = b.[szDoId]
                        AND b.szProductId <> ''
                    LEFT JOIN BOS_AR_Customer cust ON a.[szCustId] = cust.szCustId
                    LEFT JOIN BOS_PI_Employee c ON a.[szSalesId] = c.[szEmployeeId]
                    LEFT JOIN BOS_AR_Customer d ON a.[szCustId] = d.[szCustId]
                    LEFT JOIN BOS_INV_Product e ON b.szProductId= e.szProductId
                    LEFT JOIN BOS_GL_Workplace f ON a.szWorkplaceId= f.szWorkplaceId
                    LEFT JOIN BOS_TIN_CustTaxIndConfig tax ON a.[szCustId] = tax.szCustId
                    LEFT JOIN BOS_AR_FCustChgInvAddress taxinv ON a.[szCustId] = taxinv.szCustId
                WHERE
                    $filter_tanggal
                    AND a.bApplied= '1'
                    AND a.bVoid= '0'
                    AND a.[szWorkplaceId] NOT IN ('777')
                    $filter_pt
                    $filter_brand
                    $filter_depo
                    $filter_tipe
                ORDER BY
                    a.[dtmDelivery]";
            $data = DB::connection('bosnet_live')->select($query);
            if (count($data) > 0) {
                try {
                    DB::beginTransaction();

                    foreach ($data as $row) {
                        // Convert the entire object to an array
                        $rowArray = json_decode(json_encode($row), true);

                        // Apply type conversions
                        if (isset($rowArray['qty_pcs'])) {
                            $rowArray['qty_pcs'] = (int)$rowArray['qty_pcs']; // Convert to integer
                        }
                        $rowArray['is_checked'] = (int)$rowArray['is_checked'];
                        $rowArray['is_downloaded'] = (int)$rowArray['is_downloaded'];
                        $rowArray['type_pajak'] = (int)$rowArray['type_pajak'];
                        $rowArray['no_fp'] = (int)$rowArray['no_fp'];
                        $rowArray['kode_jenis_fp'] = (int)$rowArray['kode_jenis_fp'];
                        $rowArray['fp_normal_pengganti'] = (int)$rowArray['fp_normal_pengganti'];

                        // Convert any objects or arrays to JSON
                        foreach ($rowArray as $key => $value) {
                            if (is_object($value) || is_array($value)) {
                                $rowArray[$key] = json_encode($value);
                            }
                        }

                        // Check if record already exists
                        // Assuming no_invoice and no_do together form a unique identifier
                        $exists = DB::connection('sqlsrv')
                            ->table('pajak_keluaran_details')
                            ->where('no_invoice', $rowArray['no_invoice'])
                            ->where('no_do', $rowArray['no_do'])
                            ->where('kode_produk', $rowArray['kode_produk'])
                            ->exists();

                        if ($exists) {
                            // Create a copy of the row data for updating
                            $updateData = $rowArray;

                            // Remove is_checked and is_downloaded from the update data
                            unset($updateData['is_checked']);
                            unset($updateData['is_downloaded']);

                            // Update existing record without changing is_checked and is_downloaded
                            DB::connection('sqlsrv')
                                ->table('pajak_keluaran_details')
                                ->where('no_invoice', $rowArray['no_invoice'])
                                ->where('no_do', $rowArray['no_do'])
                                ->where('kode_produk', $rowArray['kode_produk'])
                                ->update($updateData);
                        } else {
                            // Insert new record
                            DB::connection('sqlsrv')
                                ->table('pajak_keluaran_details')
                                ->insert($rowArray);
                        }
                    }

                    DB::commit();
                    Log::info('Berhasil update/insert pajak keluaran detail dari live db');
                    return true;
                } catch (\Throwable $th) {
                    Log::error("Failed to insert data from live db. " . $th->getMessage());
                    DB::rollBack();
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            Log::error("Failed! " . $th->getMessage());
            return false;
        }
    }
}
