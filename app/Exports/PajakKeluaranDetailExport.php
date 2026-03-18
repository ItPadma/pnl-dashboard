<?php

namespace App\Exports;

use App\Exports\Concerns\BuildsPajakKeluaranQuery;
use App\Models\PajakKeluaranDetail;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class PajakKeluaranDetailExport implements FromQuery, WithChunkReading, WithEvents, WithHeadings, WithMapping
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
        $this->initQueryBuilder();
    }

    public function headings(): array
    {
        // Ambil nama kolom dari tabel pajak_keluaran_details
        $columns = Schema::getColumnListing('pajak_keluaran_details');

        // Jika Anda ingin mengabaikan beberapa kolom tertentu, Anda dapat menggunakan array_filter
        // sebagai contoh untuk mengabaikan kolom 'id' dan 'created_at':
        $columns = array_filter($columns, function ($column) {
            return ! in_array($column, ['id', 'is_checked', 'is_downloaded', 'created_at', 'updated_at']);
        });

        return $columns;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('AB:AB')->getNumberFormat()->setFormatCode('@');
            },
        ];
    }

    public function query()
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
}
