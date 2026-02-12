<?php

namespace App\Exports;

use App\Exports\Sheets\DetailFakturSheet;
use App\Exports\Sheets\FakturSheet;
use App\Models\MasterDepo;
use App\Models\PajakKeluaranDetail;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PajakKeluaranTemplateExport implements WithMultipleSheets
{
    protected $tipe;

    protected $pt;

    protected $brand;

    protected $depo;

    protected $periode;

    protected $chstatus;

    protected $npwpPenjual = '0027139484612000';

    protected $idTkuPenjual = '0027139484612000000000';

    public function __construct(
        string $tipe,
        $pt = [],
        $brand = [],
        $depo = [],
        $periode = null,
        $chstatus = null
    ) {
        $this->tipe = $tipe;
        $this->pt = $pt;
        $this->brand = $brand;
        $this->depo = $depo;
        $this->periode = $periode;
        $this->chstatus = $chstatus;
    }

    public function sheets(): array
    {
        // Build query: checked, not downloaded, filtered by tipe
        $query = PajakKeluaranDetail::query();
        $this->applyFilters($query);

        $this->applyTipeFilter($query);

        $records = $query->get();

        // Group by invoice for Faktur sheet
        $invoiceGroups = [];
        $invoiceOrder = [];

        foreach ($records as $record) {
            $invoiceKey = $record->no_invoice;

            if (! isset($invoiceGroups[$invoiceKey])) {
                $invoiceOrder[] = $invoiceKey;
                $invoiceGroups[$invoiceKey] = [
                    'no_invoice' => $record->no_invoice,
                    'tgl_faktur_pajak' => $record->tgl_faktur_pajak,
                    'npwp_customer' => $record->npwp_customer,
                    'nik' => $record->nik,
                    'nama_sesuai_npwp' => $record->nama_sesuai_npwp,
                    'nama_customer_sistem' => $record->nama_customer_sistem,
                    'alamat_npwp_lengkap' => $record->alamat_npwp_lengkap,
                    'alamat_sistem' => $record->alamat_sistem,
                    'id_tku_pembeli' => $record->id_tku_pembeli,
                    'kode_jenis_fp' => $record->kode_jenis_fp,
                    'products' => [],
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
                    'baris_faktur' => $barisFaktur,
                    'barang_jasa' => $product->barang_jasa,
                    'nama_produk' => $product->nama_produk,
                    'satuan' => $product->satuan,
                    'hargasatuan_sblm_ppn' => $product->hargasatuan_sblm_ppn,
                    'qty_pcs' => $product->qty_pcs,
                    'disc' => $product->disc,
                    'dpp' => $product->dpp,
                    'dpp_lain' => $product->dpp_lain,
                    'ppn' => $product->ppn,
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
                    (tipe_ppn = 'PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1))
                    OR (has_moved = 'y' AND moved_to = 'pkp')
                ");
                break;

            case 'pkpnppn':
                $query->whereRaw("
                    (tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1))
                    OR (has_moved = 'y' AND moved_to = 'pkpnppn')
                ");
                break;

            case 'npkp':
                $query->whereRaw("
                    (tipe_ppn = 'PPN' AND (hargatotal_sblm_ppn > 0 OR hargatotal_sblm_ppn <= -1000000) AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1))
                    OR (has_moved = 'y' AND moved_to = 'npkp')
                ");
                break;

            case 'npkpnppn':
                $query->whereRaw("
                    (tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n' AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1))
                    OR (has_moved = 'y' AND moved_to = 'npkpnppn')
                ");
                break;

            case 'retur':
                $query->whereRaw(
                    "qty_pcs < 0 AND hargatotal_sblm_ppn >= -1000000 AND has_moved = 'n' OR moved_to = 'retur'"
                );
                break;
            case 'nonstandar':
                $query->whereRaw(
                    "(jenis = 'non-standar' AND has_moved = 'n') OR (has_moved = 'y' AND moved_to = 'nonstandar')"
                );
                break;
        }
    }

    /**
     * Mark exported records as downloaded.
     */
    protected function markAsDownloaded(): void
    {
        if (! empty($this->chstatus) && $this->chstatus !== 'checked-ready2download') {
            return;
        }

        $query = PajakKeluaranDetail::query();
        $this->applyFilters($query);

        $this->applyTipeFilter($query);

        $query->update(['is_downloaded' => 1]);
    }

    protected function applyFilters($query): void
    {
        $pt = is_array($this->pt) ? $this->pt : [$this->pt];
        $brand = is_array($this->brand) ? $this->brand : [$this->brand];
        $depo = is_array($this->depo) ? $this->depo : [$this->depo];

        if (! empty($pt) && ! in_array('all', $pt)) {
            $query->whereIn('company', $pt);
        }
        if (! empty($brand) && ! in_array('all', $brand)) {
            $query->whereIn('brand', $brand);
        }
        $userInfo = getLoggedInUserInfo();
        $userDepos = $userInfo ? $userInfo->depo : ['all'];
        if (! is_array($userDepos)) {
            $userDepos = [$userDepos];
        }

        if ($userInfo && ! in_array('all', $userDepos)) {
            $allowedDepos = MasterDepo::whereIn('code', $userDepos)
                ->get()
                ->pluck('name')
                ->toArray();

            if (! empty($depo) && ! in_array('all', $depo)) {
                $requestedDepos = MasterDepo::whereIn('code', $depo)
                    ->get()
                    ->pluck('name')
                    ->toArray();
                $validDepos = array_intersect($requestedDepos, $allowedDepos);
                if (! empty($validDepos)) {
                    $query->whereIn('depo', $validDepos);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                if (! empty($allowedDepos)) {
                    $query->whereIn('depo', $allowedDepos);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        } else {
            if (! empty($depo) && ! in_array('all', $depo)) {
                $depoNames = MasterDepo::whereIn('code', $depo)
                    ->get()
                    ->pluck('name')
                    ->toArray();
                if (! empty($depoNames)) {
                    $query->whereIn('depo', $depoNames);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        }
        if (! empty($this->periode)) {
            $periodeParts = explode(' - ', $this->periode);
            if (count($periodeParts) === 2) {
                $periodeAwal = \Carbon\Carbon::createFromFormat('d/m/Y', $periodeParts[0])->format('Y-m-d');
                $periodeAkhir = \Carbon\Carbon::createFromFormat('d/m/Y', $periodeParts[1])->format('Y-m-d');
            } else {
                $periodeAwal = \Carbon\Carbon::createFromFormat('d/m/Y', $this->periode)->format('Y-m-d');
                $periodeAkhir = \Carbon\Carbon::createFromFormat('d/m/Y', $this->periode)->format('Y-m-d');
            }
            $query->whereBetween('tgl_faktur_pajak', [$periodeAwal, $periodeAkhir]);
        }
        if (empty($this->chstatus) || $this->chstatus === 'checked-ready2download') {
            $query->where('is_checked', 1);
            $query->where('is_downloaded', 0);
        } elseif ($this->chstatus !== 'all') {
            switch ($this->chstatus) {
                case 'checked-downloaded':
                    $query->where('is_checked', 1);
                    $query->where('is_downloaded', 1);
                    break;
                case 'unchecked':
                    $query->where('is_checked', 0);
                    break;
            }
        }
    }
}
