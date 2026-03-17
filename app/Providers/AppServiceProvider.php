<?php

namespace App\Providers;

use App\Models\MasterBrand;
use App\Models\MasterCompany;
use App\Models\MasterDepo;
use App\Observers\MasterDataObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers for master data models to handle cache invalidation
        MasterCompany::observe(MasterDataObserver::class);
        MasterBrand::observe(MasterDataObserver::class);
        MasterDepo::observe(MasterDataObserver::class);
    }
}
