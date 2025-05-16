<?php

namespace App\Exports;

use App\Models\MasterPkp;
use App\Models\PajakKeluaranDetail;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class PajakKeluaranDetailExport implements FromCollection, WithHeadings, WithEvents
{
    protected $tipe;

    public function __construct($tipe)
    {
        $this->tipe = $tipe;
    }

    public function headings(): array
    {
        // Ambil nama kolom dari tabel pajak_keluaran_details
        $columns = Schema::getColumnListing('pajak_keluaran_details');

        // Jika Anda ingin mengabaikan beberapa kolom tertentu, Anda dapat menggunakan array_filter
        // sebagai contoh untuk mengabaikan kolom 'id' dan 'created_at':
        $columns = array_filter($columns, function ($column) {
            return !in_array($column, ['id', 'is_checked', 'is_downloaded', 'created_at', 'updated_at']);
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


    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = PajakKeluaranDetail::query();
        $query->where('is_checked', 1);
        $query->where('is_downloaded', 0);
        $pkp = MasterPkp::all()->pluck('IDPelanggan')->toArray();
        if ($this->tipe == 'pkp') {
            $query->whereIn('customer_id', $pkp);
            $query->where('tipe_ppn', 'PPN');
        }
        if ($this->tipe == 'pkpnppn') {
            $query->whereIn('customer_id', $pkp);
            $query->where('tipe_ppn', 'NON-PPN');
        }
        if ($this->tipe == 'npkp') {
            $query->whereNotIn('customer_id', $pkp);
            $query->where('tipe_ppn', 'PPN');
        }
        if ($this->tipe == 'npkpnppn') {
            $query->whereNotIn('customer_id', $pkp);
            $query->where('tipe_ppn', 'NON-PPN');
        }
        if ($this->tipe == 'retur') {
            $query->where('qty_pcs', '<', 0);
        }
        $data = $query->select(array_diff(Schema::getColumnListing((new PajakKeluaranDetail)->getTable()), ['id', 'is_checked', 'is_downloaded', 'created_at', 'updated_at']))->get()
        ->map(function ($item) {
            $item->nik = "'{$item->nik}";
            $item->kode_produk = "'{$item->kode_produk}";
            return $item;
        });
        PajakKeluaranDetail::where('is_checked', 1)
            ->where(function ($query) {
                $query->where('is_downloaded', 0);
                $pkp = MasterPkp::all()->pluck('IDPelanggan')->toArray();
                if ($this->tipe == 'pkp') {
                    $query->whereIn('customer_id', $pkp);
                    $query->where('tipe_ppn', 'PPN');
                }
                if ($this->tipe == 'pkpnppn') {
                    $query->whereIn('customer_id', $pkp);
                    $query->where('tipe_ppn', 'NON-PPN');
                }
                if ($this->tipe == 'npkp') {
                    $query->whereNotIn('customer_id', $pkp);
                    $query->where('tipe_ppn', 'PPN');
                }
                if ($this->tipe == 'npkpnppn') {
                    $query->whereNotIn('customer_id', $pkp);
                    $query->where('tipe_ppn', 'NON-PPN');
                }
                if ($this->tipe == 'retur') {
                    $query->where('qty_pcs', '<', 0);
                }
            })
            ->update(['is_downloaded' => 1]);
        return $data;
    }
}
