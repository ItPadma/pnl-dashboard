<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\BuildsPajakKeluaranQuery;
use App\Models\PajakKeluaranDetail;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class StreamingDetailFakturSheet implements FromQuery, WithChunkReading, WithEvents, WithHeadings, WithMapping, WithTitle
{
    use BuildsPajakKeluaranQuery;

    protected array $tipe;

    protected $pt;

    protected $brand;

    protected $depo;

    protected $periode;

    protected $chstatus;

    protected array $refTipe;

    protected array $refSatuan;

    /**
     * Track the current invoice being processed to detect boundaries.
     */
    protected ?string $currentInvoice = null;

    /**
     * Sequential baris counter, incremented for each new invoice.
     */
    protected int $barisCounter = 0;

    /**
     * Cached satuan lookup map (built once, reused for all rows).
     */
    private ?array $satuanMap = null;

    public function __construct(
        $tipe,
        $pt = [],
        $brand = [],
        $depo = [],
        $periode = null,
        $chstatus = null,
        array $refTipe = [],
        array $refSatuan = []
    ) {
        $this->tipe = $this->normalizeTipe($tipe);
        $this->pt = $pt;
        $this->brand = $brand;
        $this->depo = $depo;
        $this->periode = $periode;
        $this->chstatus = $chstatus;
        $this->refTipe = $refTipe;
        $this->refSatuan = $refSatuan;
        $this->cachedPkpIds = null;
        $this->initQueryBuilder();
    }

    public function title(): string
    {
        return 'DetailFaktur';
    }

    public function headings(): array
    {
        return [
            'Baris', 'Barang/Jasa', 'Kode Barang Jasa', 'Nama Barang/Jasa',
            'Nama Satuan Ukur', 'Harga Satuan', 'Jumlah Barang Jasa',
            'Total Diskon', 'DPP', 'DPP Nilai Lain', 'Tarif PPN', 'PPN',
            'Tarif PPnBM', 'PPnBM',
        ];
    }

    public function query(): Builder
    {
        $query = PajakKeluaranDetail::query();

        $query->select([
            'id', 'no_invoice', 'barang_jasa', 'nama_produk', 'satuan',
            'hargasatuan_sblm_ppn', 'qty_pcs', 'disc',
        ]);

        $this->applyFilters($query, useDownloadCheck: true);
        $this->applyTipeFilter($query);

        $query->orderBy('no_invoice');
        $query->orderBy('id');

        return $query;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function map($row): array
    {
        // Track invoice boundaries for baris_faktur numbering.
        // Each time we see a new no_invoice, we increment the counter.
        if ($this->currentInvoice !== $row->no_invoice) {
            $this->currentInvoice = $row->no_invoice;
            $this->barisCounter++;
        }

        $hargaSatuan = round(floatval($row->hargasatuan_sblm_ppn ?? 0), 2);
        $qty = floatval($row->qty_pcs ?? 0);
        $disc = round(floatval($row->disc ?? 0), 2);
        $dpp = round(($qty * $hargaSatuan) - $disc, 2);
        $dppLain = round($dpp * 11 / 12, 2);
        $tarifPpn = 12;
        $ppn = round($dppLain * $tarifPpn / 100, 2);

        $barangJasa = 'A';
        if (isset($row->barang_jasa)) {
            $bj = strtoupper(trim($row->barang_jasa));
            if ($bj === 'B' || $bj === 'JASA') {
                $barangJasa = 'B';
            }
        }

        return [
            $this->barisCounter,
            $barangJasa,
            '000000',
            $row->nama_produk ?? '-',
            $this->mapSatuan($row->satuan ?? 'PCS'),
            $hargaSatuan,
            $qty,
            $disc,
            $dpp,
            $dppLain,
            $tarifPpn,
            $ppn,
            0,
            0,
        ];
    }

    protected function mapSatuan(?string $satuan): string
    {
        if (empty($satuan)) {
            return 'UM.0033';
        }

        $this->satuanMap ??= $this->buildSatuanMap();

        return $this->satuanMap[strtoupper(trim($satuan))] ?? 'UM.0033';
    }

    private function buildSatuanMap(): array
    {
        $map = [];
        foreach ($this->refSatuan as $keterangan => $kode) {
            $map[strtoupper(trim($keterangan))] = $kode;
        }

        return $map;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Bold the header row
                $sheet->getStyle('A1:N1')->getFont()->setBold(true);

                // Apply number formatting to monetary columns.
                // We can't use count($this->data) since it's streaming,
                // so use getHighestRow() instead.
                $lastRow = $sheet->getHighestRow();
                if ($lastRow > 1) {
                    $sheet->getStyle("F2:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("H2:H{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("I2:I{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("J2:J{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("L2:L{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                }

                // Add "End" marker row after all data
                $endRow = $lastRow + 1;
                $sheet->setCellValue("A{$endRow}", 'End');
            },
        ];
    }
}
