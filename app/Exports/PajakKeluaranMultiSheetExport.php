<?php

namespace App\Exports;

use App\Exports\Concerns\BuildsPajakKeluaranQuery;
use App\Exports\Sheets\PajakKeluaranTipeSheet;
use App\Models\PajakKeluaranDetail;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PajakKeluaranMultiSheetExport implements WithMultipleSheets
{
    use BuildsPajakKeluaranQuery;

    /**
     * Array of tipe values to export as separate sheets.
     */
    protected array $tipe;

    protected $pt;

    protected $brand;

    protected $depo;

    protected $periode;

    protected $chstatus;

    /**
     * Human-readable label mapping for each tipe value.
     */
    protected const TIPE_LABELS = [
        'pkp' => 'PKP',
        'pkpnppn' => 'PKP Non-PPN',
        'npkp' => 'Non-PKP',
        'npkpnppn' => 'Non-PKP Non-PPN',
        'retur' => 'Retur',
        'nonstandar' => 'Non Standar',
        'pembatalan' => 'Pembatalan',
        'koreksi' => 'Koreksi',
        'pending' => 'Pending',
    ];

    public function __construct(
        $tipe,
        $pt = [],
        $brand = [],
        $depo = [],
        $periode = null,
        $chstatus = null,
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
     * Return one sheet per selected tipe value.
     *
     * Sheets are ordered by the canonical tipe order and only include
     * tipes that have matching data (empty sheets are skipped).
     *
     * @throws \RuntimeException if no sheets are produced
     */
    public function sheets(): array
    {
        $sheets = [];

        foreach (self::ALL_TIPES as $tipe) {
            if (! in_array($tipe, $this->tipe)) {
                continue;
            }

            if (! $this->hasDataForTipe($tipe)) {
                continue;
            }

            $sheets[] = new PajakKeluaranTipeSheet(
                singleTipe: $tipe,
                sheetTitle: self::TIPE_LABELS[$tipe],
                pt: $this->pt,
                brand: $this->brand,
                depo: $this->depo,
                periode: $this->periode,
                chstatus: $this->chstatus,
            );
        }

        if (empty($sheets)) {
            throw new \RuntimeException('Tidak ada data yang sesuai dengan filter yang dipilih.');
        }

        return $sheets;
    }

    /**
     * Mark exported records as downloaded.
     *
     * Call this from the controller after the download succeeds.
     */
    public function markAsDownloaded(): void
    {
        if (empty($this->chstatus) || $this->chstatus === 'checked-ready2download' || $this->chstatus === 'checked-downloaded') {
            $updateQuery = PajakKeluaranDetail::query();
            $this->applyFilters($updateQuery);
            $this->applyTipeFilter($updateQuery);
            $updateQuery->where('is_downloaded', 0);
            $updateQuery->update(['is_downloaded' => 1]);
        }
    }

    /**
     * Check whether a specific tipe has any data rows with current filters applied.
     */
    protected function hasDataForTipe(string $tipe): bool
    {
        $query = PajakKeluaranDetail::query();
        $this->applyFilters($query);

        $pkpIds = $this->getActivePkpIds();
        $pkpEmpty = empty($pkpIds);

        $query->where(function ($q) use ($tipe, $pkpIds, $pkpEmpty) {
            $this->applySingleTipeCondition($q, $tipe, $pkpIds, $pkpEmpty);
        });

        return $query->exists();
    }
}
