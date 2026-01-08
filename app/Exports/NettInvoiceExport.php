<?php

namespace App\Exports;

use App\Models\NettInvoiceDetail;
use App\Models\NettInvoiceHeader;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class NettInvoiceExport implements FromCollection, WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            'ID Transaksi',
            'PT',
            'Principal',
            'Depo',
            'Kode Pelanggan',
            'Nama Pelanggan',
            'No Invoice',
            'Nilai Invoice Original',
            'Nilai Invoice Nett',
            'Bulan',
            'Tahun',
            'Status',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Format number columns
                $event->sheet->getDelegate()->getStyle('H:I')->getNumberFormat()->setFormatCode('#,##0.00');
            },
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Get netted invoices that haven't been downloaded
        $data = DB::table('nett_invoice_headers as nih')
            ->join('pajak_keluaran_details as pkd', 'nih.no_invoice', '=', 'pkd.no_invoice')
            ->select(
                'nih.id_transaksi',
                'nih.pt',
                'nih.principal',
                'nih.depo',
                'pkd.customer_id as kode_pelanggan',
                'pkd.nama_customer_sistem as nama_pelanggan',
                'nih.no_invoice',
                'nih.invoice_value_original',
                'nih.invoice_value_nett',
                'nih.mp_bulan',
                'nih.mp_tahun',
                'nih.status'
            )
            ->where('nih.is_checked', 1)
            ->where('nih.is_downloaded', 0)
            ->groupBy(
                'nih.id_transaksi',
                'nih.pt',
                'nih.principal',
                'nih.depo',
                'pkd.customer_id',
                'pkd.nama_customer_sistem',
                'nih.no_invoice',
                'nih.invoice_value_original',
                'nih.invoice_value_nett',
                'nih.mp_bulan',
                'nih.mp_tahun',
                'nih.status'
            )
            ->get();

        // Update is_downloaded flag after getting data
        NettInvoiceHeader::where('is_checked', 1)
            ->where('is_downloaded', 0)
            ->update(['is_downloaded' => 1]);

        return $data;
    }
}
