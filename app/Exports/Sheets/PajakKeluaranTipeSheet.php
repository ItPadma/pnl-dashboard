<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\BuildsPajakKeluaranQuery;
use App\Models\PajakKeluaranDetail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class PajakKeluaranTipeSheet implements FromQuery, WithChunkReading, WithEvents, WithHeadings, WithMapping, WithTitle
{
    use BuildsPajakKeluaranQuery;

    /**
     * The sheet title (human-readable).
     */
    protected string $sheetTitle;

    /**
     * The single tipe value for this sheet.
     */
    protected string $singleTipe;

    /**
     * Tipe value array (single-element) used by BuildsPajakKeluaranQuery trait.
     */
    protected array $tipe;

    /**
     * Common filter parameters.
     */
    protected $pt;

    protected $brand;

    protected $depo;

    protected $periode;

    protected $chstatus;

    public function __construct(
        string $singleTipe,
        string $sheetTitle,
        $pt = [],
        $brand = [],
        $depo = [],
        $periode = null,
        $chstatus = null,
    ) {
        $this->singleTipe = $singleTipe;
        $this->sheetTitle = $sheetTitle;
        $this->pt = $pt;
        $this->brand = $brand;
        $this->depo = $depo;
        $this->periode = $periode;
        $this->chstatus = $chstatus;
        $this->tipe = [$singleTipe];
        $this->cachedPkpIds = null;
        $this->initQueryBuilder();
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    /**
     * Column headers for the sheet (same as PajakKeluaranDetailExport).
     */
    public function headings(): array
    {
        $columns = Schema::getColumnListing('pajak_keluaran_details');

        $columns = array_filter($columns, function ($column) {
            return ! in_array($column, ['id', 'is_checked', 'is_downloaded', 'created_at', 'updated_at']);
        });

        return array_values($columns);
    }

    public function query(): Builder
    {
        $query = PajakKeluaranDetail::query()
            ->select(
                array_diff(
                    Schema::getColumnListing((new PajakKeluaranDetail)->getTable()),
                    ['id', 'is_checked', 'is_downloaded', 'created_at', 'updated_at'],
                ),
            );

        $this->applyFilters($query);
        $this->applyTipeFilter($query);

        return $query;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function map($row): array
    {
        $row->nik = "'{$row->nik}";
        $row->kode_produk = "'{$row->kode_produk}";

        return $row->toArray();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('AB:AB')->getNumberFormat()->setFormatCode('@');
            },
        ];
    }
}
