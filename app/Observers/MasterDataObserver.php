<?php

namespace App\Observers;

use App\Models\MasterBrand;
use App\Models\MasterCompany;
use App\Models\MasterDepo;
use App\Services\MasterDataCacheService;
use Illuminate\Support\Facades\Log;

/**
 * Observer for master data models to handle cache invalidation.
 *
 * This observer automatically clears relevant cache when master data
 * is created, updated, or deleted.
 */
class MasterDataObserver
{
    private MasterDataCacheService $cacheService;

    public function __construct(MasterDataCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the MasterCompany "created" event.
     */
    public function created(MasterCompany|MasterBrand|MasterDepo $model): void
    {
        $this->clearCache($model, 'created');
    }

    /**
     * Handle the "updated" event.
     */
    public function updated(MasterCompany|MasterBrand|MasterDepo $model): void
    {
        $this->clearCache($model, 'updated');
    }

    /**
     * Handle the "deleted" event.
     */
    public function deleted(MasterCompany|MasterBrand|MasterDepo $model): void
    {
        $this->clearCache($model, 'deleted');
    }

    /**
     * Handle the "restored" event (for soft deletes).
     */
    public function restored(MasterCompany|MasterBrand|MasterDepo $model): void
    {
        $this->clearCache($model, 'restored');
    }

    /**
     * Clear the appropriate cache based on model type.
     *
     * @param  string  $action  The action that triggered the cache clear
     */
    private function clearCache(MasterCompany|MasterBrand|MasterDepo $model, string $action): void
    {
        $modelType = class_basename($model);
        $modelId = $model->id ?? $model->code ?? 'unknown';

        Log::info("Master data {$action}, clearing cache", [
            'model' => $modelType,
            'id' => $modelId,
        ]);

        match (true) {
            $model instanceof MasterCompany => $this->cacheService->clearCompaniesCache(),
            $model instanceof MasterBrand => $this->cacheService->clearBrandsCache(),
            $model instanceof MasterDepo => $this->cacheService->clearDeposCache(),
            default => Log::warning("Unknown model type for cache invalidation: {$modelType}"),
        };
    }
}
