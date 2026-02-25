<?php

namespace App\Exports;

use App\Models\MasterDepo;
use App\Models\MasterPkp;
use App\Models\PajakKeluaranDetail;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class PajakKeluaranDetailExport implements FromCollection, WithEvents, WithHeadings
{
    protected $tipe;

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
        $this->tipe = $tipe;
        $this->pt = $pt;
        $this->brand = $brand;
        $this->depo = $depo;
        $this->periode = $periode;
        $this->chstatus = $chstatus;
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

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = PajakKeluaranDetail::query();
        $this->applyFilters($query);
        $this->applyTipeFilter($query);
        $data = $query
            ->select(
                array_diff(
                    Schema::getColumnListing((new PajakKeluaranDetail)->getTable()),
                    ['id', 'is_checked', 'is_downloaded', 'created_at', 'updated_at'],
                ),
            )
            ->get()
            ->map(function ($item) {
                $item->nik = "'{$item->nik}";
                $item->kode_produk = "'{$item->kode_produk}";

                return $item;
            });

        if (empty($this->chstatus) || $this->chstatus === 'checked-ready2download') {
            $updateQuery = PajakKeluaranDetail::query();
            $this->applyFilters($updateQuery);
            $this->applyTipeFilter($updateQuery);
            $updateQuery->update(['is_downloaded' => 1]);
        }

        return $data;
    }

    protected function applyFilters($query): void
    {
        $pt = is_array($this->pt) ? $this->pt : [$this->pt];
        $brand = is_array($this->brand) ? $this->brand : [$this->brand];
        $depo = is_array($this->depo) ? $this->depo : [$this->depo];

        if (! empty($pt) && ! in_array('all', $pt)) {
            $query->whereIn('company', $pt);
        }
        if (! empty($brand) && ! in_array('all', $brand)) {
            $query->whereIn('brand', $brand);
        }
        $userInfo = getLoggedInUserInfo();
        $userDepos = $userInfo ? $userInfo->depo : ['all'];
        if (! is_array($userDepos)) {
            $userDepos = [$userDepos];
        }

        if ($userInfo && ! in_array('all', $userDepos)) {
            $allowedDepos = MasterDepo::whereIn('code', $userDepos)
                ->get()
                ->pluck('name')
                ->toArray();

            if (! empty($depo) && ! in_array('all', $depo)) {
                $requestedDepos = MasterDepo::whereIn('code', $depo)
                    ->get()
                    ->pluck('name')
                    ->toArray();
                $validDepos = array_intersect($requestedDepos, $allowedDepos);
                if (! empty($validDepos)) {
                    $query->whereIn('depo', $validDepos);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                if (! empty($allowedDepos)) {
                    $query->whereIn('depo', $allowedDepos);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        } else {
            if (! empty($depo) && ! in_array('all', $depo)) {
                $depoNames = MasterDepo::whereIn('code', $depo)
                    ->get()
                    ->pluck('name')
                    ->toArray();
                if (! empty($depoNames)) {
                    $query->whereIn('depo', $depoNames);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        }
        if (! empty($this->periode)) {
            $periodeParts = explode(' - ', $this->periode);
            if (count($periodeParts) === 2) {
                $periodeAwal = \Carbon\Carbon::createFromFormat('d/m/Y', $periodeParts[0])->format('Y-m-d');
                $periodeAkhir = \Carbon\Carbon::createFromFormat('d/m/Y', $periodeParts[1])->format('Y-m-d');
            } else {
                $periodeAwal = \Carbon\Carbon::createFromFormat('d/m/Y', $this->periode)->format('Y-m-d');
                $periodeAkhir = \Carbon\Carbon::createFromFormat('d/m/Y', $this->periode)->format('Y-m-d');
            }
            $query->whereBetween('tgl_faktur_pajak', [$periodeAwal, $periodeAkhir]);
        }
        if (empty($this->chstatus) || $this->chstatus === 'checked-ready2download') {
            $query->where('is_checked', 1);
            $query->where('is_downloaded', 0);
        } elseif ($this->chstatus !== 'all') {
            switch ($this->chstatus) {
                case 'checked-downloaded':
                    $query->where('is_checked', 1);
                    $query->where('is_downloaded', 1);
                    break;
                case 'unchecked':
                    $query->where('is_checked', 0);
                    break;
            }
        }
    }

    protected function applyTipeFilter($query): void
    {
        switch ($this->tipe) {
            case 'pkp':
                $query->where(function ($q) {
                    $q->where(function ($inner) {
                        $inner->where('tipe_ppn', 'PPN')
                            ->where('qty_pcs', '>', 0)
                            ->where('has_moved', 'n')
                            ->whereRaw("customer_id IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)")
                            ->standardNik();
                    })->orWhere(function ($inner) {
                        $inner->where('has_moved', 'y')
                            ->where('moved_to', 'pkp');
                    });
                });
                break;
            case 'pkpnppn':
                $query->where(function ($q) {
                    $q->where(function ($inner) {
                        $inner->where('tipe_ppn', 'NON-PPN')
                            ->where('qty_pcs', '>', 0)
                            ->where('has_moved', 'n')
                            ->whereRaw("customer_id IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)")
                            ->standardNik();
                    })->orWhere(function ($inner) {
                        $inner->where('has_moved', 'y')
                            ->where('moved_to', 'pkpnppn');
                    });
                });
                break;
            case 'npkp':
                $query->where(function ($q) {
                    $q->where(function ($inner) {
                        $inner->where('tipe_ppn', 'PPN')
                            ->where(function ($harga) {
                                $harga->where('hargatotal_sblm_ppn', '>', 0)
                                    ->orWhere('hargatotal_sblm_ppn', '<=', -1000000);
                            })
                            ->where('has_moved', 'n')
                            ->whereRaw("customer_id NOT IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)")
                            ->standardNik();
                    })->orWhere(function ($inner) {
                        $inner->where('has_moved', 'y')
                            ->where('moved_to', 'npkp');
                    });
                });
                break;
            case 'npkpnppn':
                $query->where(function ($q) {
                    $q->where(function ($inner) {
                        $inner->where('tipe_ppn', 'NON-PPN')
                            ->where('qty_pcs', '>', 0)
                            ->where('has_moved', 'n')
                            ->whereRaw("customer_id NOT IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)")
                            ->standardNik();
                    })->orWhere(function ($inner) {
                        $inner->where('has_moved', 'y')
                            ->where('moved_to', 'npkpnppn');
                    });
                });
                break;
            case 'retur':
                $query->where(function ($q) {
                    $q->where(function ($inner) {
                        $inner->where('qty_pcs', '<', 0)
                            ->where('hargatotal_sblm_ppn', '>=', -1000000)
                            ->where('has_moved', 'n')
                            ->standardNik();
                    })->orWhere('moved_to', 'retur');
                });
                break;
            case 'nonstandar':
                $query->where(function ($q) {
                    $q->where(function ($inner) {
                        $inner->where('jenis', 'non-standar')
                            ->where('has_moved', 'n');
                    })->orWhere(function ($inner) {
                        $inner->where('has_moved', 'y')
                            ->where('moved_to', 'nonstandar');
                    });
                });
                break;
            case 'pembatalan':
                $query->where('has_moved', 'y')->where('moved_to', 'pembatalan');
                break;
            case 'koreksi':
                $query->where('has_moved', 'y')->where('moved_to', 'koreksi');
                break;
            case 'pending':
                $query->where('has_moved', 'y')->where('moved_to', 'pending');
                break;
        }
    }
}
