<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\BuildsPajakKeluaranQuery;
use App\Models\PajakKeluaranDetail;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class StreamingFakturSheet extends DefaultValueBinder implements FromQuery, WithChunkReading, WithCustomValueBinder, WithEvents, WithHeadings, WithMapping, WithTitle
{
    use BuildsPajakKeluaranQuery;

    /**
     * Filter parameters.
     */
    protected array $tipe;

    protected $pt;

    protected $brand;

    protected $depo;

    protected $periode;

    protected $chstatus;

    /**
     * Static Faktur header values.
     */
    protected string $npwpPenjual;

    protected string $idTkuPenjual;

    /**
     * Master references for validation.
     */
    protected array $refKodeTransaksi;

    protected array $refJenisIdPembeli;

    protected array $refKodeNegara;

    /**
     * Sequential baris counter (1-indexed) for invoice rows.
     */
    protected int $barisCounter = 0;

    public function __construct(
        $tipe,
        $pt = [],
        $brand = [],
        $depo = [],
        $periode = null,
        $chstatus = null,
        string $npwpPenjual = '0027139484612000',
        string $idTkuPenjual = '0027139484612000000000',
        array $refKodeTransaksi = [],
        array $refJenisIdPembeli = [],
        array $refKodeNegara = []
    ) {
        $this->tipe = $this->normalizeTipe($tipe);
        $this->pt = $pt;
        $this->brand = $brand;
        $this->depo = $depo;
        $this->periode = $periode;
        $this->chstatus = $chstatus;
        $this->npwpPenjual = $npwpPenjual;
        $this->idTkuPenjual = $idTkuPenjual;
        $this->refKodeTransaksi = $refKodeTransaksi;
        $this->refJenisIdPembeli = $refJenisIdPembeli;
        $this->refKodeNegara = $refKodeNegara;

        $this->cachedPkpIds = null;
        $this->initQueryBuilder();
    }

    public function title(): string
    {
        return 'Faktur';
    }

    /**
     * Column headers for the Faktur sheet.
     */
    public function headings(): array
    {
        return [
            'Baris',
            'Tanggal Faktur',
            'Jenis Faktur',
            'Kode Transaksi',
            'Keterangan Tambahan',
            'Dokumen Pendukung',
            'Period Dok Pendukung',
            'Referensi',
            'Cap Fasilitas',
            'ID TKU Penjual',
            'NPWP/NIK Pembeli',
            'Jenis ID Pembeli',
            'Negara Pembeli',
            'Nomor Dokumen Pembeli',
            'Nama Pembeli',
            'Alamat Pembeli',
            'Email Pembeli',
            'ID TKU Pembeli',
        ];
    }

    /**
     * Build the query for fetching faktur rows.
     * Uses SELECT DISTINCT to get exactly one representative row per unique no_invoice,
     * ordered by no_invoice for deterministic processing.
     */
    public function query(): Builder
    {
        $query = PajakKeluaranDetail::query();

        // Select only the columns needed for the Faktur sheet.
        // DISTINCT ensures one row per unique combination (effectively per invoice
        // since no_invoice is the primary grouping column).
        $query->select([
            'no_invoice',
            'no_do',
            'tgl_faktur_pajak',
            'npwp_customer',
            'nik',
            'nama_sesuai_npwp',
            'nama_customer_sistem',
            'alamat_npwp_lengkap',
            'alamat_sistem',
            'id_tku_pembeli',
            'kode_jenis_fp',
        ]);

        $this->applyFilters($query, useDownloadCheck: true);
        $this->applyTipeFilter($query);

        $query->distinct();
        $query->orderBy('no_invoice');

        return $query;
    }

    /**
     * Chunk size for streaming.
     */
    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * Map each query result row to the Faktur output format.
     */
    public function map($row): array
    {
        $this->barisCounter++;

        $npwpCustomer = $row->npwp_customer ?? '';
        $nik = $row->nik ?? '';

        // Determine Jenis ID Pembeli and NPWP/NIK value
        $jenisId = 'National ID';
        $npwpNik = $nik;

        if (! empty($npwpCustomer) && $npwpCustomer !== '0' && $npwpCustomer !== '-') {
            if (in_array('TIN', $this->refJenisIdPembeli)) {
                $jenisId = 'TIN';
            }
            $npwpNik = $npwpCustomer;
        } else {
            if (in_array('National ID', $this->refJenisIdPembeli)) {
                $jenisId = 'National ID';
            }
        }

        // Determine Nama Pembeli
        $namaPembeli = ! empty($row->nama_sesuai_npwp)
            ? $row->nama_sesuai_npwp
            : ($row->nama_customer_sistem ?? '-');

        // Determine Alamat Pembeli
        $alamatPembeli = ! empty($row->alamat_npwp_lengkap)
            ? $row->alamat_npwp_lengkap
            : ($row->alamat_sistem ?? '-');

        // Format tanggal
        $tglFaktur = $row->tgl_faktur_pajak ?? '';
        if ($tglFaktur instanceof \DateTime || $tglFaktur instanceof \Carbon\Carbon) {
            $tglFaktur = $tglFaktur->format('d/m/Y');
        } elseif (is_string($tglFaktur) && ! empty($tglFaktur)) {
            try {
                $tglFaktur = \Carbon\Carbon::parse($tglFaktur)->format('d/m/Y');
            } catch (\Exception $e) {
                // keep as-is
            }
        }

        // Kode Transaksi
        $kodeTransaksi = $row->kode_jenis_fp ?? '04';
        if (is_numeric($kodeTransaksi) && intval($kodeTransaksi) < 10) {
            $kodeTransaksi = str_pad($kodeTransaksi, 2, '0', STR_PAD_LEFT);
        }

        // Referensi
        $noDo = trim((string) ($row->no_do ?? ''));
        $noInvoice = trim((string) ($row->no_invoice ?? ''));
        $referensi = '';
        if ($noDo !== '' && $noInvoice !== '') {
            $referensi = $noDo.'_'.$noInvoice;
        } elseif ($noDo !== '') {
            $referensi = $noDo;
        } elseif ($noInvoice !== '') {
            $referensi = $noInvoice;
        }

        // Negara Pembeli
        $negaraPembeli = 'IDN';
        if (! empty($this->refKodeNegara) && in_array('IDN', $this->refKodeNegara)) {
            $negaraPembeli = 'IDN';
        }

        return [
            $this->barisCounter,               // Baris (sequential 1-indexed)
            $tglFaktur,                         // Tanggal Faktur
            'Normal',                           // Jenis Faktur
            $kodeTransaksi,                     // Kode Transaksi
            null,                               // Keterangan Tambahan
            null,                               // Dokumen Pendukung
            null,                               // Period Dok Pendukung
            $referensi,                         // Referensi
            null,                               // Cap Fasilitas
            $this->idTkuPenjual,                // ID TKU Penjual
            $npwpNik,                           // NPWP/NIK Pembeli
            $jenisId,                           // Jenis ID Pembeli
            $negaraPembeli,                     // Negara Pembeli
            '-',                                // Nomor Dokumen Pembeli
            $namaPembeli,                       // Nama Pembeli
            $alamatPembeli,                     // Alamat Pembeli
            null,                               // Email Pembeli
            $row->id_tku_pembeli ?? '',         // ID TKU Pembeli
        ];
    }

    /**
     * Register events for static header rows and formatting.
     *
     * BeforeSheet: Insert 2 rows at top so that:
     *   Row 1 = NPWP Penjual header
     *   Row 2 = empty
     *   Row 3 = column headings (pushed down by insertNewRowBefore)
     *   Row 4+ = data rows (pushed down as well)
     *
     * AfterSheet: Apply formatting and append "End" marker.
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Insert 2 rows at top: row 1 = NPWP header, row 2 = empty.
                // This pushes headings (written by WithHeadings) and all data
                // down by 2 rows, matching the original FakturSheet layout.
                $sheet->insertNewRowBefore(1, 2);

                // Row 1: NPWP Penjual header
                $sheet->setCellValue('A1', 'NPWP Penjual');
                $sheet->setCellValue('C1', $this->npwpPenjual);
                $sheet->getStyle('A1:R1')->getFont()->setBold(true);

                // Row 2: intentionally left empty
            },
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Row 3: column headings (pushed down by BeforeSheet insert)
                $sheet->getStyle('A3:R3')->getFont()->setBold(true);

                // Format NPWP/ID columns as text to preserve leading zeros
                $sheet->getStyle('C1')->getNumberFormat()->setFormatCode('@');
                $sheet->getStyle('J:J')->getNumberFormat()->setFormatCode('@');
                $sheet->getStyle('K:K')->getNumberFormat()->setFormatCode('@');
                $sheet->getStyle('R:R')->getNumberFormat()->setFormatCode('@');

                // Add "End" marker row after all data
                $highestRow = $sheet->getHighestRow();
                $endRow = $highestRow + 1;
                $sheet->setCellValue("A{$endRow}", 'End');
            },
        ];
    }

    /**
     * Bind specific columns as explicit string to preserve leading zeros.
     */
    public function bindValue(Cell $cell, $value): bool
    {
        $column = $cell->getColumn();

        // C - NPWP Penjual (Row 1)
        // J - ID TKU Penjual
        // K - NPWP/NIK Pembeli
        // R - ID TKU Pembeli
        if (in_array($column, ['C', 'J', 'K', 'R'])) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
