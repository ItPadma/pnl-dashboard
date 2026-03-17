<?php

namespace App\Exports;

use App\Exports\Concerns\BuildsPajakKeluaranQuery;
use App\Exports\Sheets\StreamingDetailFakturSheet;
use App\Exports\Sheets\StreamingFakturSheet;
use App\Models\MasterRefIdPembeli;
use App\Models\MasterRefKodeNegara;
use App\Models\MasterRefKodeTransaksi;
use App\Models\MasterRefSatuanUkur;
use App\Models\MasterRefTipe;
use App\Models\PajakKeluaranDetail;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PajakKeluaranTemplateExport implements WithMultipleSheets
{
    use BuildsPajakKeluaranQuery;

    /**
     * Array of tipe values to filter.
     */
    protected array $tipe;

    protected $pt;

    protected $brand;

    protected $depo;

    protected $periode;

    protected $chstatus;

    protected string $npwpPenjual = '0027139484612000';

    protected string $idTkuPenjual = '0027139484612000000000';

    public function __construct(
        $tipe,
        $pt = [],
        $brand = [],
        $depo = [],
        $periode = null,
        $chstatus = null
    ) {
        $this->tipe = $this->normalizeTipe($tipe);
        $this->pt = $pt;
        $this->brand = $brand;
        $this->depo = $depo;
        $this->periode = $periode;
        $this->chstatus = $chstatus;
        $this->cachedPkpIds = null;
        $this->initQueryBuilder();
    }

    /**
     * Return sheet instances that stream data from the database
     * instead of loading everything into memory.
     */
    public function sheets(): array
    {
        $refKodeTransaksi = MasterRefKodeTransaksi::where('is_active', true)->pluck('kode')->toArray();
        $refJenisIdPembeli = MasterRefIdPembeli::where('is_active', true)->pluck('kode')->toArray();
        $refKodeNegara = MasterRefKodeNegara::where('is_active', true)->pluck('kode')->toArray();
        $refTipe = MasterRefTipe::where('is_active', true)->pluck('kode')->toArray();
        $refSatuan = MasterRefSatuanUkur::where('is_active', true)->pluck('kode', 'keterangan')->toArray();

        return [
            new StreamingFakturSheet(
                $this->tipe,
                $this->pt,
                $this->brand,
                $this->depo,
                $this->periode,
                $this->chstatus,
                $this->npwpPenjual,
                $this->idTkuPenjual,
                $refKodeTransaksi,
                $refJenisIdPembeli,
                $refKodeNegara,
            ),
            new StreamingDetailFakturSheet(
                $this->tipe,
                $this->pt,
                $this->brand,
                $this->depo,
                $this->periode,
                $this->chstatus,
                $refTipe,
                $refSatuan,
            ),
        ];
    }

    /**
     * Mark exported records as downloaded.
     *
     * Call this from the controller after the download succeeds.
     */
    public function markAsDownloaded(): void
    {
        if (! empty($this->chstatus) && $this->chstatus !== 'checked-ready2download') {
            return;
        }

        $query = PajakKeluaranDetail::query();
        $this->applyFilters($query, useDownloadCheck: true);
        $this->applyTipeFilter($query);

        // Guard: only update records not yet marked as downloaded
        $query->where('is_downloaded', 0);
        $query->update(['is_downloaded' => 1]);
    }
}
