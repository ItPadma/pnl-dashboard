<?php

namespace App\Services;

use App\Models\MasterBrand;
use App\Models\MasterCompany;
use App\Models\MasterDepo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service for caching master data (PT/Company, Brand, Depo).
 *
 * This service provides centralized caching for frequently accessed master data
 * to reduce database load and improve response times.
 */
class MasterDataCacheService
{
    // Cache TTL constants (in seconds)
    public const TTL_COMPANIES = 3600;          // 1 hour

    public const TTL_BRANDS = 3600;             // 1 hour

    public const TTL_DEPOS = 3600;              // 1 hour

    public const TTL_DEPO_NAMES = 1800;         // 30 minutes

    public const TTL_BRANDS_BY_COMPANY = 1800;  // 30 minutes

    // Cache key constants
    public const KEY_COMPANIES_ALL = 'master:companies:all';

    public const KEY_BRANDS_ALL = 'master:brands:all';

    public const KEY_DEPOS_ALL = 'master:depos:all';

    public const KEY_DEPO_NAMES_PREFIX = 'master:depos:names:';

    public const KEY_BRANDS_BY_COMPANY_PREFIX = 'master:brands:company:';

    /**
     * The cache store to use.
     */
    protected string $cacheStore;

    /**
     * Create a new MasterDataCacheService instance.
     *
     * @param  string|null  $cacheStore  Override the default cache store
     */
    public function __construct(?string $cacheStore = null)
    {
        $this->cacheStore = $cacheStore ?? config('cache.stores.redis') ? 'redis' : config('cache.default');
    }

    /**
     * Get the cache store instance.
     */
    protected function cache(): \Illuminate\Contracts\Cache\Repository
    {
        return Cache::store($this->cacheStore);
    }

    /**
     * Get all companies with caching.
     *
     * @return Collection<int, MasterCompany>
     */
    public function getCompanies(): Collection
    {
        try {
            return $this->cache()->remember(
                self::KEY_COMPANIES_ALL,
                self::TTL_COMPANIES,
                fn () => MasterCompany::select(['id', 'code', 'name'])->get()
            );
        } catch (\Throwable $e) {
            Log::warning('Redis cache failed for companies, falling back to database', [
                'error' => $e->getMessage(),
            ]);

            return MasterCompany::select(['id', 'code', 'name'])->get();
        }
    }

    /**
     * Get brands with optional company filter.
     *
     * @param  array|null  $companies  Array of company codes to filter by
     * @return Collection<int, MasterBrand>
     */
    public function getBrands(?array $companies = null): Collection
    {
        try {
            // No filter - return all brands
            if ($companies === null || empty($companies)) {
                return $this->cache()->remember(
                    self::KEY_BRANDS_ALL,
                    self::TTL_BRANDS,
                    fn () => MasterBrand::select(['id', 'code', 'name'])->get()
                );
            }

            // Filter by companies - cache with specific key
            sort($companies);
            $key = self::KEY_BRANDS_BY_COMPANY_PREFIX.hash('sha256', implode(',', $companies));

            return $this->cache()->remember(
                $key,
                self::TTL_BRANDS_BY_COMPANY,
                fn () => MasterBrand::select(['id', 'code', 'name'])
                    ->whereHas('multiCompProdMappings', function ($query) use ($companies): void {
                        $query->whereIn('szCompanyID', $companies);
                    })->get()
            );
        } catch (\Throwable $e) {
            Log::warning('Redis cache failed for brands, falling back to database', [
                'error' => $e->getMessage(),
                'companies' => $companies,
            ]);

            if ($companies === null || empty($companies)) {
                return MasterBrand::select(['id', 'code', 'name'])->get();
            }

            return MasterBrand::select(['id', 'code', 'name'])
                ->whereHas('multiCompProdMappings', function ($query) use ($companies): void {
                    $query->whereIn('szCompanyID', $companies);
                })->get();
        }
    }

    /**
     * Get brand by single company code.
     *
     * @param  string  $company  Company code
     * @return Collection<int, MasterBrand>
     */
    public function getBrandsByCompany(string $company): Collection
    {
        return $this->getBrands([$company]);
    }

    /**
     * Get all depos with caching.
     *
     * @return Collection<int, MasterDepo>
     */
    public function getDepos(): Collection
    {
        try {
            return $this->cache()->remember(
                self::KEY_DEPOS_ALL,
                self::TTL_DEPOS,
                fn () => MasterDepo::select(['id', 'code', 'name'])->get()
            );
        } catch (\Throwable $e) {
            Log::warning('Redis cache failed for depos, falling back to database', [
                'error' => $e->getMessage(),
            ]);

            return MasterDepo::select(['id', 'code', 'name'])->get();
        }
    }

    /**
     * Get depo names by codes (frequently used in RegulerController).
     *
     * This method is optimized for the common pattern in RegulerController
     * where we need to convert depo codes to depo names.
     *
     * @param  array  $codes  Array of depo codes
     * @return array<int, string> Array of depo names
     */
    public function getDepoNamesByCodes(array $codes): array
    {
        if (empty($codes)) {
            return [];
        }

        try {
            sort($codes);
            $key = self::KEY_DEPO_NAMES_PREFIX.hash('sha256', implode(',', $codes));

            return $this->cache()->remember(
                $key,
                self::TTL_DEPO_NAMES,
                fn () => MasterDepo::whereIn('code', $codes)->pluck('name')->toArray()
            );
        } catch (\Throwable $e) {
            Log::warning('Redis cache failed for depo names, falling back to database', [
                'error' => $e->getMessage(),
                'codes' => $codes,
            ]);

            return MasterDepo::whereIn('code', $codes)->pluck('name')->toArray();
        }
    }

    /**
     * Get depo collection by codes.
     *
     * @param  array  $codes  Array of depo codes
     * @return Collection<int, MasterDepo>
     */
    public function getDeposByCodes(array $codes): Collection
    {
        if (empty($codes)) {
            return collect([]);
        }

        // For specific codes, we query directly as caching many small subsets
        // may not be efficient
        return MasterDepo::whereIn('code', $codes)->get();
    }

    /**
     * Clear all companies cache.
     */
    public function clearCompaniesCache(): void
    {
        try {
            $this->cache()->forget(self::KEY_COMPANIES_ALL);
            Log::info('Companies cache cleared');
        } catch (\Throwable $e) {
            Log::error('Failed to clear companies cache', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Clear all brands cache.
     */
    public function clearBrandsCache(): void
    {
        try {
            $this->cache()->forget(self::KEY_BRANDS_ALL);
            $this->clearKeysByPattern(self::KEY_BRANDS_BY_COMPANY_PREFIX.'*');
            Log::info('Brands cache cleared');
        } catch (\Throwable $e) {
            Log::error('Failed to clear brands cache', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Clear all depos cache.
     */
    public function clearDeposCache(): void
    {
        try {
            $this->cache()->forget(self::KEY_DEPOS_ALL);
            $this->clearKeysByPattern(self::KEY_DEPO_NAMES_PREFIX.'*');
            Log::info('Depos cache cleared');
        } catch (\Throwable $e) {
            Log::error('Failed to clear depos cache', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Clear all master data cache.
     */
    public function clearAll(): void
    {
        $this->clearCompaniesCache();
        $this->clearBrandsCache();
        $this->clearDeposCache();
        Log::info('All master data cache cleared');
    }

    /**
     * Clear cache by pattern using Redis directly.
     *
     * @param  string  $pattern  Pattern to match (e.g., 'master:depos:names:*')
     */
    private function clearKeysByPattern(string $pattern): void
    {
        try {
            $redis = Cache::store('redis')->getRedis();
            $prefix = config('cache.prefix', '');
            $fullPattern = $prefix.$pattern;

            $iterator = null;
            while (($keys = $redis->scan($iterator, $fullPattern, 100)) !== false) {
                if (! empty($keys)) {
                    $redis->del($keys);
                }
                if ($iterator === 0) {
                    break;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to clear cache by pattern', [
                'pattern' => $pattern,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Warm up cache by preloading all master data.
     * Useful for cache warming on deployment or scheduled tasks.
     */
    public function warmUp(): void
    {
        Log::info('Starting cache warm-up for master data');

        try {
            $this->getCompanies();
            $this->getBrands();
            $this->getDepos();

            Log::info('Cache warm-up completed successfully');
        } catch (\Throwable $e) {
            Log::error('Cache warm-up failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get cache statistics for monitoring.
     *
     * @return array{companies_cached: bool, brands_cached: bool, depos_cached: bool, error?: string} Statistics about cached master data
     */
    public function getCacheStats(): array
    {
        try {
            return [
                'companies_cached' => $this->cache()->has(self::KEY_COMPANIES_ALL),
                'brands_cached' => $this->cache()->has(self::KEY_BRANDS_ALL),
                'depos_cached' => $this->cache()->has(self::KEY_DEPOS_ALL),
            ];
        } catch (\Throwable $e) {
            Log::warning('Failed to get cache stats', ['error' => $e->getMessage()]);

            return [
                'companies_cached' => false,
                'brands_cached' => false,
                'depos_cached' => false,
                'error' => 'Cache unavailable: '.$e->getMessage(),
            ];
        }
    }
}
