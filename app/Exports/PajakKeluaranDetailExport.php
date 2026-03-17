<?php

namespace App\Exports;

use App\Models\MasterKabupatenKota;
use App\Models\MasterPkp;
use App\Models\PajakKeluaranDetail;
use App\Services\MasterDataCacheService;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class PajakKeluaranDetailExport implements FromCollection, WithEvents, WithHeadings
{
    /**
     * Array of tipe values to filter.
     */
    protected array $tipe;

    protected $pt;

    protected $brand;

    protected $depo;

    protected $periode;

    protected $chstatus;

    /**
     * Cached PKP IDs for the export.
     */
    protected ?array $cachedPkpIds = null;

    /**
     * Cache service for master data.
     */
    protected MasterDataCacheService $cacheService;

    /**
     * All available tipe values.
     */
    protected const ALL_TIPES = ['pkp', 'pkpnppn', 'npkp', 'npkpnppn', 'retur', 'nonstandar', 'pembatalan', 'koreksi', 'pending'];

    public function __construct(
        $tipe,
        $pt = [],
        $brand = [],
        $depo = [],
        $periode = null,
        $chstatus = null
    ) {
        // Normalize tipe to array for consistent handling
        if (is_array($tipe)) {
            $this->tipe = $tipe;
        } else {
            $this->tipe = [$tipe];
        }

        // Remove 'all' and dedupe
        $this->tipe = array_unique(array_filter($this->tipe, fn ($t) => $t !== 'all'));

        // If empty after filtering, treat as all
        if (empty($this->tipe)) {
            $this->tipe = self::ALL_TIPES;
        }

        $this->pt = $pt;
        $this->brand = $brand;
        $this->depo = $depo;
        $this->periode = $periode;
        $this->chstatus = $chstatus;
        $this->cacheService = app(MasterDataCacheService::class);
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
     * Get active PKP customer IDs, cached for the export.
     */
    protected function getActivePkpIds(): array
    {
        if ($this->cachedPkpIds === null) {
            // Include whereNotNull to prevent NULL values in IN clause
            $this->cachedPkpIds = MasterPkp::where('is_active', true)
                ->whereNotNull('IDPelanggan')
                ->pluck('IDPelanggan')
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->toArray();
        }

        return $this->cachedPkpIds;
    }

    /**
     * Escape an array of IDs for safe SQL IN clause usage.
     * Prevents SQL injection by escaping single quotes.
     *
     * @param  array  $ids  Array of ID strings
     * @return string Comma-separated, quoted and escaped ID list
     */
    protected function escapeSqlIdList(array $ids): string
    {
        return implode(',', array_map(function ($id) {
            return "'".str_replace("'", "''", (string) $id)."'";
        }, $ids));
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

        if (empty($this->chstatus) || $this->chstatus === 'checked-ready2download' || $this->chstatus === 'checked-downloaded') {
            // Single UPDATE statement is efficient for marking records as downloaded
            $updateQuery = PajakKeluaranDetail::query();
            $this->applyFilters($updateQuery);
            $this->applyTipeFilter($updateQuery);
            $updateQuery->where('is_downloaded', 0);
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

        // OPTIMIZED: Use cached depo names
        $userInfo = getLoggedInUserInfo();
        $userDepos = $userInfo ? $userInfo->depo : ['all'];
        if (! is_array($userDepos)) {
            $userDepos = [$userDepos];
        }

        if ($userInfo && ! in_array('all', $userDepos)) {
            // Use cache service instead of direct query
            $allowedDepos = $this->cacheService->getDepoNamesByCodes($userDepos);

            if (! empty($depo) && ! in_array('all', $depo)) {
                $requestedDepos = $this->cacheService->getDepoNamesByCodes($depo);
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
                $depoNames = $this->cacheService->getDepoNamesByCodes($depo);
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
        // OPTIMIZED: Cache PKP IDs once for the entire export
        $pkpIds = $this->getActivePkpIds();
        $pkpEmpty = empty($pkpIds);

        // Use OR logic to combine multiple tipe filters
        $query->where(function ($mainQuery) use ($pkpIds, $pkpEmpty) {
            foreach ($this->tipe as $tipe) {
                $mainQuery->orWhere(function ($q) use ($tipe, $pkpIds, $pkpEmpty) {
                    switch ($tipe) {
                        case 'pkp':
                            $q->where(function ($inner) use ($pkpIds, $pkpEmpty) {
                                $inner->where('tipe_ppn', 'PPN')
                                    ->where('qty_pcs', '>', 0)
                                    ->where('has_moved', 'n')
                                    ->standardNik();
                                // Use whereRaw with escaped ID list to avoid SQL Server parameter limit
                                if (! $pkpEmpty) {
                                    $inner->whereRaw('customer_id IN ('.$this->escapeSqlIdList($pkpIds).')');
                                } else {
                                    $inner->whereRaw('1 = 0');
                                }
                            })->orWhere(function ($inner) {
                                $inner->where('has_moved', 'y')
                                    ->where('moved_to', 'pkp');
                            });
                            break;
                        case 'pkpnppn':
                            $q->where(function ($inner) use ($pkpIds, $pkpEmpty) {
                                $inner->where('tipe_ppn', 'NON-PPN')
                                    ->where('qty_pcs', '>', 0)
                                    ->where('has_moved', 'n')
                                    ->standardNik();
                                // Use whereRaw with escaped ID list to avoid SQL Server parameter limit
                                if (! $pkpEmpty) {
                                    $inner->whereRaw('customer_id IN ('.$this->escapeSqlIdList($pkpIds).')');
                                } else {
                                    $inner->whereRaw('1 = 0');
                                }
                            })->orWhere(function ($inner) {
                                $inner->where('has_moved', 'y')
                                    ->where('moved_to', 'pkpnppn');
                            });
                            break;
                        case 'npkp':
                            $q->where(function ($inner) use ($pkpIds) {
                                $inner->where('tipe_ppn', 'PPN')
                                    ->where(function ($harga) {
                                        $harga->where('hargatotal_sblm_ppn', '>', 0)
                                            ->orWhere('hargatotal_sblm_ppn', '<=', -1000000);
                                    })
                                    ->where('has_moved', 'n')
                                    ->standardNik();
                                // Use whereRaw with escaped ID list to avoid SQL Server parameter limit
                                if (! empty($pkpIds)) {
                                    $inner->whereRaw('customer_id NOT IN ('.$this->escapeSqlIdList($pkpIds).')');
                                }
                            })->orWhere(function ($inner) {
                                $inner->where('has_moved', 'y')
                                    ->where('moved_to', 'npkp');
                            });
                            break;
                        case 'npkpnppn':
                            $q->where(function ($inner) use ($pkpIds) {
                                $inner->where('tipe_ppn', 'NON-PPN')
                                    ->where('qty_pcs', '>', 0)
                                    ->where('has_moved', 'n')
                                    ->standardNik();
                                // Use whereRaw with escaped ID list to avoid SQL Server parameter limit
                                if (! empty($pkpIds)) {
                                    $inner->whereRaw('customer_id NOT IN ('.$this->escapeSqlIdList($pkpIds).')');
                                }
                            })->orWhere(function ($inner) {
                                $inner->where('has_moved', 'y')
                                    ->where('moved_to', 'npkpnppn');
                            });
                            break;
                        case 'retur':
                            $q->where(function ($inner) {
                                $inner->where('qty_pcs', '<', 0)
                                    ->where('hargatotal_sblm_ppn', '>=', -1000000)
                                    ->where('has_moved', 'n')
                                    ->standardNik();
                            })->orWhere('moved_to', 'retur');
                            break;
                        case 'nonstandar':
                            $this->applyNonStandarScope($q);
                            break;
                        case 'pembatalan':
                            $q->where('has_moved', 'y')->where('moved_to', 'pembatalan');
                            break;
                        case 'koreksi':
                            $q->where('has_moved', 'y')->where('moved_to', 'koreksi');
                            break;
                        case 'pending':
                            $q->where('has_moved', 'y')->where('moved_to', 'pending');
                            break;
                    }
                });
            }
        });
    }

    /**
     * Apply nonstandar scope (same logic as RegulerController).
     */
    protected function applyNonStandarScope($query): void
    {
        $pkpIds = $this->getActivePkpIds();

        $query->where(function ($q) use ($pkpIds) {
            // Condition 1: manually moved to nonstandar
            $q->orWhere(function ($manual) {
                $manual->where('has_moved', 'y')
                    ->where('moved_to', 'nonstandar');
            });

            // Condition 2: NIK format issues
            $kabupatenIds = $this->getKabupatenKotaIds();
            $kabsArrayStr = empty($kabupatenIds) ? "''" : implode(',', array_map(function ($id) {
                return "'".str_replace("'", "''", $id)."'";
            }, $kabupatenIds));

            $q->orWhere(function ($nikIssue) use ($kabsArrayStr) {
                $nikIssue->where('has_moved', 'n')
                    ->where(function ($nikCondition) use ($kabsArrayStr) {
                        $nikCondition->where(function ($invalidId) {
                            $invalidId->whereRaw('LEN(nik_digits) NOT IN (15, 16)')
                                ->orWhereRaw("REPLACE(nik_digits, '0', '') = ''");
                        })
                            ->orWhere(function ($nikOnly) use ($kabsArrayStr) {
                                $nikOnly->whereRaw('LEN(nik_digits) = 16')
                                    ->where(function ($nikRule) use ($kabsArrayStr) {
                                        $nikRule->whereRaw("RIGHT(nik_digits, 3) = '000'")
                                            ->orWhereRaw("LEFT(nik_digits, 4) NOT IN ($kabsArrayStr)");
                                    });
                            });
                    });
            });

            // Condition 3: fallback — doesn't match any standard category
            $q->orWhereRaw($this->nonStandarFallbackConditionSql($pkpIds));
        });
    }

    /**
     * Get MasterKabupatenKota IDs.
     */
    protected function getKabupatenKotaIds(): array
    {
        return MasterKabupatenKota::pluck('id')->toArray();
    }

    /**
     * Build nonstandar fallback condition SQL.
     */
    protected function nonStandarFallbackConditionSql(array $pkpIds = []): string
    {
        if (empty($pkpIds)) {
            return "(has_moved = 'n' AND NOT ("
                ."(tipe_ppn = 'PPN' AND qty_pcs > 0 AND 1=0)"
                ." OR (tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND 1=0)"
                ." OR (tipe_ppn = 'PPN' AND (hargatotal_sblm_ppn > 0 OR hargatotal_sblm_ppn <= -1000000))"
                ." OR (tipe_ppn = 'NON-PPN' AND qty_pcs > 0)"
                .' OR (qty_pcs < 0 AND hargatotal_sblm_ppn >= -1000000)'
                .'))';
        }

        $idList = $this->escapeSqlIdList($pkpIds);

        return "(has_moved = 'n' AND NOT ("
            ."(tipe_ppn = 'PPN' AND qty_pcs > 0 AND customer_id IN ($idList))"
            ." OR (tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND customer_id IN ($idList))"
            ." OR (tipe_ppn = 'PPN' AND (hargatotal_sblm_ppn > 0 OR hargatotal_sblm_ppn <= -1000000) AND customer_id NOT IN ($idList))"
            ." OR (tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND customer_id NOT IN ($idList))"
            .' OR (qty_pcs < 0 AND hargatotal_sblm_ppn >= -1000000)'
            .'))';
    }
}
