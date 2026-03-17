<?php

namespace App\Exports\Concerns;

use App\Models\MasterKabupatenKota;
use App\Models\MasterPkp;
use App\Models\PajakKeluaranDetail;
use App\Services\MasterDataCacheService;
use Illuminate\Database\Eloquent\Builder;

trait BuildsPajakKeluaranQuery
{
    /**
     * All available tipe values.
     */
    protected const ALL_TIPES = ['pkp', 'pkpnppn', 'npkp', 'npkpnppn', 'retur', 'nonstandar', 'pembatalan', 'koreksi', 'pending'];

    /**
     * Cached PKP IDs for the export.
     */
    protected ?array $cachedPkpIds = null;

    /**
     * Cache service for master data lookups.
     */
    protected MasterDataCacheService $cacheService;

    /**
     * Initialize the cache service (call from constructor).
     */
    protected function initQueryBuilder(): void
    {
        $this->cacheService = app(MasterDataCacheService::class);
    }

    /**
     * Create a base query for PajakKeluaranDetail with all filters applied.
     */
    protected function buildBaseQuery(): Builder
    {
        $query = PajakKeluaranDetail::query();
        $this->applyFilters($query);
        $this->applyTipeFilter($query);

        return $query;
    }

    /**
     * Apply common filters (pt, brand, depo, periode, chstatus).
     *
     * When $useDownloadCheck is true (template export), the default status
     * also requires is_downloaded = 0. When false (detail export), it does not.
     */
    protected function applyFilters($query, bool $useDownloadCheck = false): void
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

        // Use cached depo names via MasterDataCacheService
        $userInfo = getLoggedInUserInfo();
        $userDepos = $userInfo ? $userInfo->depo : ['all'];
        if (! is_array($userDepos)) {
            $userDepos = [$userDepos];
        }

        if ($userInfo && ! in_array('all', $userDepos)) {
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
            if ($useDownloadCheck) {
                $query->where('is_downloaded', 0);
            }
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

    /**
     * Apply type-based filter to the query (supports multiple tipe values with OR logic).
     * Uses cached PKP IDs instead of subqueries.
     */
    protected function applyTipeFilter($query): void
    {
        $pkpIds = $this->getActivePkpIds();
        $pkpEmpty = empty($pkpIds);

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

            // Condition 3: fallback - doesn't match any standard category
            $q->orWhereRaw($this->nonStandarFallbackConditionSql($pkpIds));
        });
    }

    /**
     * Get active PKP customer IDs, cached for the export.
     */
    protected function getActivePkpIds(): array
    {
        if ($this->cachedPkpIds === null) {
            $this->cachedPkpIds = MasterPkp::where('is_active', true)
                ->whereNotNull('IDPelanggan')
                ->pluck('IDPelanggan')
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->toArray();
        }

        return $this->cachedPkpIds;
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
     *
     * Records that have has_moved = 'n' but don't match any standard
     * category (pkp, pkpnppn, npkp, npkpnppn, or retur).
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
     * Normalize tipe parameter into a validated array.
     *
     * @param  mixed  $tipe  Single tipe string or array of tipe strings
     * @return array Validated, deduplicated tipe array
     */
    protected function normalizeTipe($tipe): array
    {
        $tipes = is_array($tipe) ? $tipe : [$tipe];
        $tipes = array_unique(array_filter($tipes, fn ($t) => $t !== 'all'));

        return empty($tipes) ? self::ALL_TIPES : array_values($tipes);
    }
}
