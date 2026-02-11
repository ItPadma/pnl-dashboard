<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class FakturSheet implements FromArray, WithTitle, WithEvents
{
    protected $data;
    protected $npwpPenjual;
    protected $idTkuPenjual;

    public function __construct(array $data, string $npwpPenjual = '0027139484612000', string $idTkuPenjual = '0027139484612000000000')
    {
        $this->data = $data;
        $this->npwpPenjual = $npwpPenjual;
        $this->idTkuPenjual = $idTkuPenjual;
    }

    public function title(): string
    {
        return 'Faktur';
    }

    public function array(): array
    {
        $rows = [];

        // Row 1: NPWP Penjual header
        $row1 = array_fill(0, 18, null);
        $row1[0] = 'NPWP Penjual';
        $row1[2] = $this->npwpPenjual;
        $rows[] = $row1;

        // Row 2: empty
        $rows[] = array_fill(0, 18, null);

        // Row 3: column headers
        $rows[] = [
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

        // Row 4+: data rows
        foreach ($this->data as $index => $invoice) {
            $npwpCustomer = $invoice['npwp_customer'] ?? '';
            $nik = $invoice['nik'] ?? '';

            // Determine Jenis ID Pembeli and NPWP/NIK value
            if (!empty($npwpCustomer) && $npwpCustomer !== '0' && $npwpCustomer !== '-') {
                $jenisId = 'TIN';
                $npwpNik = $npwpCustomer;
            } else {
                $jenisId = 'National ID';
                $npwpNik = $nik;
            }

            // Determine Nama Pembeli
            $namaPembeli = !empty($invoice['nama_sesuai_npwp'])
                ? $invoice['nama_sesuai_npwp']
                : ($invoice['nama_customer_sistem'] ?? '-');

            // Determine Alamat Pembeli
            $alamatPembeli = !empty($invoice['alamat_npwp_lengkap'])
                ? $invoice['alamat_npwp_lengkap']
                : ($invoice['alamat_sistem'] ?? '-');

            // Format tanggal
            $tglFaktur = $invoice['tgl_faktur_pajak'] ?? '';
            if ($tglFaktur instanceof \DateTime || $tglFaktur instanceof \Carbon\Carbon) {
                $tglFaktur = $tglFaktur->format('d/m/Y');
            } elseif (is_string($tglFaktur) && !empty($tglFaktur)) {
                try {
                    $tglFaktur = \Carbon\Carbon::parse($tglFaktur)->format('d/m/Y');
                } catch (\Exception $e) {
                    // keep as-is
                }
            }

            // Kode Transaksi
            $kodeTransaksi = $invoice['kode_jenis_fp'] ?? '04';
            if (is_numeric($kodeTransaksi) && intval($kodeTransaksi) < 10) {
                $kodeTransaksi = str_pad($kodeTransaksi, 2, '0', STR_PAD_LEFT);
            }

            $rows[] = [
                $index + 1,                          // Baris
                $tglFaktur,                          // Tanggal Faktur
                'Normal',                            // Jenis Faktur
                $kodeTransaksi,                      // Kode Transaksi
                null,                                // Keterangan Tambahan
                null,                                // Dokumen Pendukung
                null,                                // Period Dok Pendukung
                $invoice['no_invoice'] ?? '',         // Referensi
                null,                                // Cap Fasilitas
                $this->idTkuPenjual,                 // ID TKU Penjual
                $npwpNik,                            // NPWP/NIK Pembeli
                $jenisId,                            // Jenis ID Pembeli
                'IDN',                               // Negara Pembeli
                '-',                                 // Nomor Dokumen Pembeli
                $namaPembeli,                        // Nama Pembeli
                $alamatPembeli,                      // Alamat Pembeli
                null,                                // Email Pembeli
                $invoice['id_tku_pembeli'] ?? '',     // ID TKU Pembeli
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Format NPWP columns as text to preserve leading zeros
                $sheet->getStyle('C1')->getNumberFormat()->setFormatCode('@');
                $sheet->getStyle('J:J')->getNumberFormat()->setFormatCode('@');
                $sheet->getStyle('K:K')->getNumberFormat()->setFormatCode('@');
                $sheet->getStyle('R:R')->getNumberFormat()->setFormatCode('@');

                // Bold header rows
                $sheet->getStyle('A1:R1')->getFont()->setBold(true);
                $sheet->getStyle('A3:R3')->getFont()->setBold(true);
            },
        ];
    }
}
