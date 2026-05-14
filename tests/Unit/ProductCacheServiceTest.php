<?php

namespace Tests\Unit;

use App\Services\ProductCacheService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProductCacheServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_remember_list_calls_resolver_only_once_for_identical_filters(): void
    {
        $service = new ProductCacheService;
        $calls = 0;
        $filters = ['page' => '1', 'sort_by' => 'price'];

        $first = $service->rememberList($filters, function () use (&$calls): array {
            $calls++;

            return ['payload' => 'first'];
        });
        $second = $service->rememberList($filters, function () use (&$calls): array {
            $calls++;

            return ['payload' => 'second'];
        });

        $this->assertSame(['payload' => 'first'], $first);
        $this->assertSame(['payload' => 'first'], $second);
        $this->assertSame(1, $calls);
    }

    public function test_remember_list_uses_distinct_cache_entries_for_different_filters(): void
    {
        $service = new ProductCacheService;
        $calls = 0;

        $a = $service->rememberList(['name' => 'alpha'], function () use (&$calls): array {
            $calls++;

            return ['name' => 'alpha'];
        });
        $b = $service->rememberList(['name' => 'beta'], function () use (&$calls): array {
            $calls++;

            return ['name' => 'beta'];
        });

        $this->assertSame(2, $calls);
        $this->assertSame(['name' => 'alpha'], $a);
        $this->assertSame(['name' => 'beta'], $b);
    }

    public function test_invalidate_list_increments_version_so_resolver_runs_again(): void
    {
        $service = new ProductCacheService;
        $filters = ['sort_by' => 'created_at'];
        $calls = 0;

        $service->rememberList($filters, function () use (&$calls): array {
            $calls++;

            return ['generation' => 1];
        });
        $this->assertSame(1, $calls);
        $this->assertSame(1, $service->listCacheVersion());

        $service->invalidateList();
        $this->assertSame(2, $service->listCacheVersion());

        $after = $service->rememberList($filters, function () use (&$calls): array {
            $calls++;

            return ['generation' => 2];
        });

        $this->assertSame(2, $calls);
        $this->assertSame(['generation' => 2], $after);
    }

    public function test_filter_array_key_order_does_not_create_separate_cache_buckets(): void
    {
        $service = new ProductCacheService;
        $calls = 0;

        $first = $service->rememberList(['z' => '1', 'a' => '2'], function () use (&$calls): array {
            $calls++;

            return ['unified' => true];
        });
        $second = $service->rememberList(['a' => '2', 'z' => '1'], function () use (&$calls): array {
            $calls++;

            return ['unified' => false];
        });

        $this->assertSame(1, $calls);
        $this->assertEquals($first, $second);
    }

    public function test_list_cache_version_defaults_to_one_when_version_key_absent(): void
    {
        $service = new ProductCacheService;

        $this->assertSame(1, $service->listCacheVersion());
    }
}
