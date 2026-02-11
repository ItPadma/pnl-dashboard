<?php

namespace App\Exports;

use App\Exports\Sheets\FakturSheet;
use App\Exports\Sheets\DetailFakturSheet;
use App\Models\MasterPkp;
use App\Models\PajakKeluaranDetail;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PajakKeluaranTemplateExport implements WithMultipleSheets
{
    protected $tipe;
    protected $npwpPenjual = '0027139484612000';
    protected $idTkuPenjual = '0027139484612000000000';

    public function __construct(string $tipe)
    {
        $this->tipe = $tipe;
    }

    public function sheets(): array
    {
        // Build query: checked, not downloaded, filtered by tipe
        $query = PajakKeluaranDetail::query();
        $query->where('is_checked', 1);
        $query->where('is_downloaded', 0);

        $this->applyTipeFilter($query);

        $records = $query->get();

        // Group by invoice for Faktur sheet
        $invoiceGroups = [];
        $invoiceOrder = [];

        foreach ($records as $record) {
            $invoiceKey = $record->no_invoice;

            if (!isset($invoiceGroups[$invoiceKey])) {
                $invoiceOrder[] = $invoiceKey;
                $invoiceGroups[$invoiceKey] = [
                    'no_invoice'         => $record->no_invoice,
                    'tgl_faktur_pajak'   => $record->tgl_faktur_pajak,
                    'npwp_customer'      => $record->npwp_customer,
                    'nik'                => $record->nik,
                    'nama_sesuai_npwp'   => $record->nama_sesuai_npwp,
                    'nama_customer_sistem' => $record->nama_customer_sistem,
                    'alamat_npwp_lengkap' => $record->alamat_npwp_lengkap,
                    'alamat_sistem'      => $record->alamat_sistem,
                    'id_tku_pembeli'     => $record->id_tku_pembeli,
                    'kode_jenis_fp'      => $record->kode_jenis_fp,
                    'products'           => [],
                ];
            }

            $invoiceGroups[$invoiceKey]['products'][] = $record;
        }

        // Build Faktur data (ordered, indexed from 0)
        $fakturData = [];
        foreach ($invoiceOrder as $invoiceKey) {
            $fakturData[] = $invoiceGroups[$invoiceKey];
        }

        // Build DetailFaktur data with baris_faktur reference
        $detailData = [];
        foreach ($fakturData as $fakturIndex => $invoice) {
            $barisFaktur = $fakturIndex + 1; // 1-indexed to match Faktur row

            foreach ($invoice['products'] as $product) {
                $detailData[] = [
                    'baris_faktur'         => $barisFaktur,
                    'barang_jasa'          => $product->barang_jasa,
                    'nama_produk'          => $product->nama_produk,
                    'satuan'               => $product->satuan,
                    'hargasatuan_sblm_ppn' => $product->hargasatuan_sblm_ppn,
                    'qty_pcs'              => $product->qty_pcs,
                    'disc'                 => $product->disc,
                    'dpp'                  => $product->dpp,
                    'dpp_lain'             => $product->dpp_lain,
                    'ppn'                  => $product->ppn,
                ];
            }
        }

        // Mark records as downloaded
        $this->markAsDownloaded();

        return [
            new FakturSheet($fakturData, $this->npwpPenjual, $this->idTkuPenjual),
            new DetailFakturSheet($detailData),
        ];
    }

    /**
     * Apply type-based filter to the query (same logic as RegulerController).
     */
    protected function applyTipeFilter($query): void
    {
        switch ($this->tipe) {
            case 'pkp':
                $query->whereRaw("
                    (tipe_ppn = 'PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id IN (SELECT IDPelanggan FROM master_pkp))
                    OR (has_moved = 'y' AND moved_to = 'pkp')
                ");
                break;

            case 'pkpnppn':
                $query->whereRaw("
                    (tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id IN (SELECT IDPelanggan FROM master_pkp))
                    OR (has_moved = 'y' AND moved_to = 'pkpnppn')
                ");
                break;

            case 'npkp':
                $query->whereRaw("
                    (tipe_ppn = 'PPN' AND (hargatotal_sblm_ppn > 0 OR hargatotal_sblm_ppn <= -1000000) AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp))
                    OR (has_moved = 'y' AND moved_to = 'npkp')
                ");
                break;

            case 'npkpnppn':
                $query->whereRaw("
                    (tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp))
                    OR (has_moved = 'y' AND moved_to = 'npkpnppn')
                ");
                break;

            case 'retur':
                $query->whereRaw(
                    "qty_pcs < 0 AND hargatotal_sblm_ppn >= -1000000 AND has_moved = 'n' OR moved_to = 'retur'"
                );
                break;
        }
    }

    /**
     * Mark exported records as downloaded.
     */
    protected function markAsDownloaded(): void
    {
        $query = PajakKeluaranDetail::where('is_checked', 1)
            ->where('is_downloaded', 0);

        $this->applyTipeFilter($query);

        $query->update(['is_downloaded' => 1]);
    }
}
