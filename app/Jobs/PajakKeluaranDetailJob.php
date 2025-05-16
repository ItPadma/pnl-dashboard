<?php

namespace App\Jobs;

use App\Models\PajakKeluaranDetail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PajakKeluaranDetailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('PajakKeluaranDetailJob is running');

            $dataToInsert = $this->getPendingData();

            if (!empty($dataToInsert)) {
                DB::beginTransaction();
                foreach ($dataToInsert as $data) {
                    PajakKeluaranDetail::create($data);
                }
                DB::commit();
                Log::info('PajakKeluaranDetailJob: ' . count($dataToInsert) . ' records inserted successfully');
            } else {
                Log::info('PajakKeluaranDetailJob: No data to insert');
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('PajakKeluaranDetailJob: ' . $th->getMessage());
        }
    }

     private function getPendingData()
    {
        // get last dtmDelivery + 1 day format Y-m-d
        $lastDate = PajakKeluaranDetail::max('tgl_faktur_pajak');
        $lastDate = date('Y-m-d', strtotime($lastDate . ' +1 day'));

        // get today date
        $today = date('Y-m-d');

        // get data from table pajak_keluaran_detail where created_at is today
        $query = "SELECT
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
            NO_FP = 0,
            d.[szCategory_1] AS 'brand',
            CASE
                WHEN a.bcash = 0 THEN 'CREDIT'
                ELSE 'CASH'
            END AS 'type_jual',
            KODE_JENIS_FP = 0,
            FP_NORMAL_PENGGANTI = 0,
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
            BarangJasa = 'barang_jasa',
            0 AS is_checked,
            0 AS is_downloaded,
            e.szTaxTypeId AS tipe_ppn,
            e.szCategory_9 AS company
        FROM
            [10.100.100.11].[padma_live].[dbo].[BOS_SD_FDo] a
            LEFT JOIN [10.100.100.11].[padma_live].[dbo].BOS_SD_FDoItem b ON a.[szDoId] = b.[szDoId] AND b.szProductId <> ''
            LEFT JOIN [10.100.100.11].[padma_live].[dbo].BOS_AR_Customer cust ON a.[szCustId] = cust.szCustId
            LEFT JOIN [10.100.100.11].[padma_live].[dbo].BOS_PI_Employee c ON a.[szSalesId] = c.[szEmployeeId]
            LEFT JOIN [10.100.100.11].[padma_live].[dbo].BOS_AR_Customer d ON a.[szCustId] = d.[szCustId]
            LEFT JOIN [10.100.100.11].[padma_live].[dbo].BOS_INV_Product e ON b.szProductId= e.szProductId
            LEFT JOIN [10.100.100.11].[padma_live].[dbo].BOS_GL_Workplace f ON a.szWorkplaceId= f.szWorkplaceId
            LEFT JOIN [10.100.100.11].[padma_live].[dbo].BOS_TIN_CustTaxIndConfig tax ON a.[szCustId] = tax.szCustId
            LEFT JOIN [10.100.100.11].[padma_live].[dbo].BOS_AR_FCustChgInvAddress taxinv ON a.[szCustId] = taxinv.szCustId
        WHERE
            FORMAT (a.dtmDelivery, 'yyyy-MM-dd') BETWEEN '$lastDate'
            AND '$today'
            AND a.bApplied= '1'
            AND a.bVoid= '0'
            AND a.[szWorkplaceId] NOT IN ('777')
            --AND e.szCategory_9 = 'ATP'
            --AND e.szCategory_1 = 'MOTASA'
            --AND cust.szCategory_1 = 'MOTASA'
            --AND a.[szFInvoiceId] = '999-25-0038201'
        ORDER BY
            a.[dtmDelivery]";

        $data = DB::select($query);

        return $data;
    }
}
