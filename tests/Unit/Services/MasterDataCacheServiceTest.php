<?php

namespace Tests\Unit\Services;

use App\Models\MasterBrand;
use App\Models\MasterCompany;
use App\Models\MasterDepo;
use App\Services\MasterDataCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MasterDataCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    private MasterDataCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = app(MasterDataCacheService::class);

        // Clear cache before each test
        $this->cacheService->clearAll();
    }

    protected function tearDown(): void
    {
        // Clear cache after each test
        $this->cacheService->clearAll();
        parent::tearDown();
    }

    /** @test */
    public function it_caches_companies_and_returns_collection(): void
    {
        // Create test data
        MasterCompany::create(['code' => 'PT001', 'name' => 'PT Test 1']);
        MasterCompany::create(['code' => 'PT002', 'name' => 'PT Test 2']);

        // First call - should hit database
        $result = $this->cacheService->getCompanies();

        $this->assertCount(2, $result);
        $this->assertTrue(Cache::store('redis')->has(MasterDataCacheService::KEY_COMPANIES_ALL));

        // Clear model cache to simulate fresh database query
        MasterCompany::getQuery()->getQuery()->connection = null;

        // Second call - should hit cache
        $cachedResult = $this->cacheService->getCompanies();

        $this->assertCount(2, $cachedResult);
        $this->assertEquals($result->pluck('code')->sort()->values(), $cachedResult->pluck('code')->sort()->values());
    }

    /** @test */
    public function it_caches_brands_without_filter(): void
    {
        // Create test data
        MasterBrand::create(['code' => 'BR001', 'name' => 'Brand 1']);
        MasterBrand::create(['code' => 'BR002', 'name' => 'Brand 2']);

        $result = $this->cacheService->getBrands();

        $this->assertCount(2, $result);
        $this->assertTrue(Cache::store('redis')->has(MasterDataCacheService::KEY_BRANDS_ALL));
    }

    /** @test */
    public function it_caches_depos_and_returns_collection(): void
    {
        // Create test data
        MasterDepo::create(['code' => 'DEP001', 'name' => 'Depo 1']);
        MasterDepo::create(['code' => 'DEP002', 'name' => 'Depo 2']);
        MasterDepo::create(['code' => 'DEP003', 'name' => 'Depo 3']);

        $result = $this->cacheService->getDepos();

        $this->assertCount(3, $result);
        $this->assertTrue(Cache::store('redis')->has(MasterDataCacheService::KEY_DEPOS_ALL));
    }

    /** @test */
    public function it_caches_depo_names_by_codes(): void
    {
        // Create test data
        MasterDepo::create(['code' => 'DEP001', 'name' => 'Depo Jakarta']);
        MasterDepo::create(['code' => 'DEP002', 'name' => 'Depo Bandung']);
        MasterDepo::create(['code' => 'DEP003', 'name' => 'Depo Surabaya']);

        $result = $this->cacheService->getDepoNamesByCodes(['DEP001', 'DEP002']);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertContains('Depo Jakarta', $result);
        $this->assertContains('Depo Bandung', $result);
    }

    /** @test */
    public function it_returns_empty_array_for_empty_codes(): void
    {
        $result = $this->cacheService->getDepoNamesByCodes([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function it_clears_companies_cache(): void
    {
        MasterCompany::create(['code' => 'PT001', 'name' => 'PT Test']);

        // Prime the cache
        $this->cacheService->getCompanies();
        $this->assertTrue(Cache::store('redis')->has(MasterDataCacheService::KEY_COMPANIES_ALL));

        // Clear cache
        $this->cacheService->clearCompaniesCache();

        $this->assertFalse(Cache::store('redis')->has(MasterDataCacheService::KEY_COMPANIES_ALL));
    }

    /** @test */
    public function it_clears_brands_cache(): void
    {
        MasterBrand::create(['code' => 'BR001', 'name' => 'Brand 1']);

        // Prime the cache
        $this->cacheService->getBrands();
        $this->assertTrue(Cache::store('redis')->has(MasterDataCacheService::KEY_BRANDS_ALL));

        // Clear cache
        $this->cacheService->clearBrandsCache();

        $this->assertFalse(Cache::store('redis')->has(MasterDataCacheService::KEY_BRANDS_ALL));
    }

    /** @test */
    public function it_clears_depos_cache(): void
    {
        MasterDepo::create(['code' => 'DEP001', 'name' => 'Depo 1']);

        // Prime the cache
        $this->cacheService->getDepos();
        $this->assertTrue(Cache::store('redis')->has(MasterDataCacheService::KEY_DEPOS_ALL));

        // Clear cache
        $this->cacheService->clearDeposCache();

        $this->assertFalse(Cache::store('redis')->has(MasterDataCacheService::KEY_DEPOS_ALL));
    }

    /** @test */
    public function it_clears_all_cache(): void
    {
        // Create and prime all caches
        MasterCompany::create(['code' => 'PT001', 'name' => 'PT Test']);
        MasterBrand::create(['code' => 'BR001', 'name' => 'Brand 1']);
        MasterDepo::create(['code' => 'DEP001', 'name' => 'Depo 1']);

        $this->cacheService->getCompanies();
        $this->cacheService->getBrands();
        $this->cacheService->getDepos();

        $this->assertTrue(Cache::store('redis')->has(MasterDataCacheService::KEY_COMPANIES_ALL));
        $this->assertTrue(Cache::store('redis')->has(MasterDataCacheService::KEY_BRANDS_ALL));
        $this->assertTrue(Cache::store('redis')->has(MasterDataCacheService::KEY_DEPOS_ALL));

        // Clear all
        $this->cacheService->clearAll();

        $this->assertFalse(Cache::store('redis')->has(MasterDataCacheService::KEY_COMPANIES_ALL));
        $this->assertFalse(Cache::store('redis')->has(MasterDataCacheService::KEY_BRANDS_ALL));
        $this->assertFalse(Cache::store('redis')->has(MasterDataCacheService::KEY_DEPOS_ALL));
    }

    /** @test */
    public function it_returns_cache_stats(): void
    {
        MasterCompany::create(['code' => 'PT001', 'name' => 'PT Test']);
        MasterBrand::create(['code' => 'BR001', 'name' => 'Brand 1']);
        MasterDepo::create(['code' => 'DEP001', 'name' => 'Depo 1']);

        // Before caching
        $stats = $this->cacheService->getCacheStats();
        $this->assertFalse($stats['companies_cached']);
        $this->assertFalse($stats['brands_cached']);
        $this->assertFalse($stats['depos_cached']);

        // Prime caches
        $this->cacheService->getCompanies();
        $this->cacheService->getBrands();
        $this->cacheService->getDepos();

        // After caching
        $stats = $this->cacheService->getCacheStats();
        $this->assertTrue($stats['companies_cached']);
        $this->assertTrue($stats['brands_cached']);
        $this->assertTrue($stats['depos_cached']);
    }

    /** @test */
    public function it_warms_up_cache(): void
    {
        MasterCompany::create(['code' => 'PT001', 'name' => 'PT Test']);
        MasterBrand::create(['code' => 'BR001', 'name' => 'Brand 1']);
        MasterDepo::create(['code' => 'DEP001', 'name' => 'Depo 1']);

        $this->cacheService->warmUp();

        $stats = $this->cacheService->getCacheStats();
        $this->assertTrue($stats['companies_cached']);
        $this->assertTrue($stats['brands_cached']);
        $this->assertTrue($stats['depos_cached']);
    }
}
