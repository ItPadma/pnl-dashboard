<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DetailFakturSheet implements FromArray, WithTitle, WithEvents
{
    protected $data;
    protected $refTipe;
    protected $refSatuan;

    public function __construct(array $data, array $refTipe = [], array $refSatuan = [])
    {
        $this->data = $data;
        $this->refTipe = $refTipe;
        $this->refSatuan = $refSatuan;
    }

    public function title(): string
    {
        return 'DetailFaktur';
    }

    /**
     * Map satuan from DB to Coretax code dynamically using MasterRef
     */
    public function mapSatuan(?string $satuan): string
    {
        if (empty($satuan)) {
            return 'UM.0033'; // Lainnya fallback
        }

        $upper = strtoupper(trim($satuan));

        // Find match in our reference data (keys are uppercase 'keterangan')
        // Array from DB pluck is already formatted, but we need to ensure keys match
        $map = [];
        foreach ($this->refSatuan as $keterangan => $kode) {
            $map[strtoupper(trim($keterangan))] = $kode;
        }

        return $map[$upper] ?? 'UM.0033';
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
            if (! empty($this->refTipe) && in_array('A', $this->refTipe)) {
                $barangJasa = 'A';
            }

            if (isset($detail['barang_jasa'])) {
                $bj = strtoupper(trim($detail['barang_jasa']));
                if ($bj === 'B' || $bj === 'JASA') {
                    if (in_array('B', $this->refTipe)) {
                        $barangJasa = 'B';
                    } else { // Fallback if ref misses 'B'
                        $barangJasa = 'B'; 
                    }
                }
            }

            $rows[] = [
                $detail['baris_faktur'],                          // Baris (references Faktur row number)
                $barangJasa,                                       // Barang/Jasa
                '000000',                                          // Kode Barang Jasa
                $detail['nama_produk'] ?? '-',                     // Nama Barang/Jasa
                $this->mapSatuan($detail['satuan'] ?? 'PCS'),       // Nama Satuan Ukur
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

        $rows[] = array_fill(0, 14, null);
        $rows[count($rows) - 1][0] = 'End';

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
