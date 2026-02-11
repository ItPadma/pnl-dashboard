<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DetailFakturSheet implements FromArray, WithTitle, WithEvents
{
    protected $data;

    /**
     * Mapping dari satuan DB ke kode satuan Coretax.
     * Referensi: sheet REF pada import-template.xlsx
     */
    protected static $satuanMapping = [
        'METRIK TON'    => 'UM.0001',
        'WET TON'       => 'UM.0002',
        'KILOGRAM'      => 'UM.0003',
        'KG'            => 'UM.0003',
        'GRAM'          => 'UM.0004',
        'GR'            => 'UM.0004',
        'KARAT'         => 'UM.0005',
        'KILOLITER'     => 'UM.0006',
        'KL'            => 'UM.0006',
        'LITER'         => 'UM.0007',
        'LTR'           => 'UM.0007',
        'BARREL'        => 'UM.0008',
        'MMBTU'         => 'UM.0009',
        'AMPERE'        => 'UM.0010',
        'CM3'           => 'UM.0011',
        'M2'            => 'UM.0012',
        'METER'         => 'UM.0013',
        'MTR'           => 'UM.0013',
        'INCI'          => 'UM.0014',
        'SENTIMETER'    => 'UM.0015',
        'CM'            => 'UM.0015',
        'YARD'          => 'UM.0016',
        'LUSIN'         => 'UM.0017',
        'LSN'           => 'UM.0017',
        'UNIT'          => 'UM.0018',
        'SET'           => 'UM.0019',
        'LEMBAR'        => 'UM.0020',
        'LBR'           => 'UM.0020',
        'PIECE'         => 'UM.0021',
        'PCS'           => 'UM.0021',
        'PC'            => 'UM.0021',
        'BOKS'          => 'UM.0022',
        'BOX'           => 'UM.0022',
        'TAHUN'         => 'UM.0023',
        'BULAN'         => 'UM.0024',
        'MINGGU'        => 'UM.0025',
        'HARI'          => 'UM.0026',
        'JAM'           => 'UM.0027',
        'MENIT'         => 'UM.0028',
        'PERSEN'        => 'UM.0029',
        'KEGIATAN'      => 'UM.0030',
        'LAPORAN'       => 'UM.0031',
        'BAHAN'         => 'UM.0032',
        'LAINNYA'       => 'UM.0033',
        'M3'            => 'UM.0034',
        'CM2'           => 'UM.0035',
        'DRUM'          => 'UM.0036',
        'KARTON'        => 'UM.0037',
        'KTN'           => 'UM.0037',
        'CTN'           => 'UM.0037',
        'KWH'           => 'UM.0038',
        'ROLL'          => 'UM.0039',
    ];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'DetailFaktur';
    }

    /**
     * Map satuan from DB to Coretax code.
     */
    public static function mapSatuan(?string $satuan): string
    {
        if (empty($satuan)) {
            return 'UM.0033'; // Lainnya
        }

        $upper = strtoupper(trim($satuan));
        return self::$satuanMapping[$upper] ?? 'UM.0033';
    }

    public function array(): array
    {
        $rows = [];

        // Row 1: headers
        $rows[] = [
            'Baris',
            'Barang/Jasa',
            'Kode Barang Jasa',
            'Nama Barang/Jasa',
            'Nama Satuan Ukur',
            'Harga Satuan',
            'Jumlah Barang Jasa',
            'Total Diskon',
            'DPP',
            'DPP Nilai Lain',
            'Tarif PPN',
            'PPN',
            'Tarif PPnBM',
            'PPnBM',
        ];

        // Row 2+: detail data
        foreach ($this->data as $detail) {
            $hargaSatuan = round(floatval($detail['hargasatuan_sblm_ppn'] ?? 0), 2);
            $qty = floatval($detail['qty_pcs'] ?? 0);
            $disc = round(floatval($detail['disc'] ?? 0), 2);
            $dpp = round(($qty * $hargaSatuan) - $disc, 2);
            $dppLain = round($dpp * 11 / 12, 2);
            $tarifPpn = 12;
            $ppn = round($dppLain * $tarifPpn / 100, 2);

            // Map barang_jasa: default to 'A' (Barang)
            $barangJasa = 'A';
            if (isset($detail['barang_jasa'])) {
                $bj = strtoupper(trim($detail['barang_jasa']));
                if ($bj === 'B' || $bj === 'JASA') {
                    $barangJasa = 'B';
                }
            }

            $rows[] = [
                $detail['baris_faktur'],                          // Baris (references Faktur row number)
                $barangJasa,                                       // Barang/Jasa
                '000000',                                          // Kode Barang Jasa
                $detail['nama_produk'] ?? '-',                     // Nama Barang/Jasa
                self::mapSatuan($detail['satuan'] ?? 'PCS'),       // Nama Satuan Ukur
                $hargaSatuan,                                      // Harga Satuan
                $qty,                                              // Jumlah Barang Jasa
                $disc,                                             // Total Diskon
                $dpp,                                              // DPP
                $dppLain,                                          // DPP Nilai Lain
                $tarifPpn,                                         // Tarif PPN
                $ppn,                                              // PPN
                0,                                                 // Tarif PPnBM
                0,                                                 // PPnBM
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Bold header row
                $sheet->getStyle('A1:N1')->getFont()->setBold(true);

                // Format numeric columns with 2 decimal places
                $lastRow = count($this->data) + 1;
                if ($lastRow > 1) {
                    $sheet->getStyle("F2:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("H2:H{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("I2:I{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("J2:J{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("L2:L{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                }
            },
        ];
    }
}
