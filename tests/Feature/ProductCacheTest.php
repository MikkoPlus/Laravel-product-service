<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_list_cache_is_invalidated_after_product_creation(): void
    {
        $category = Category::query()->create(['name' => 'Electronics']);
        $cacheService = app(ProductCacheService::class);

        $this->getJson('/api/products')->assertOk();
        $versionBeforeCreate = $cacheService->listCacheVersion();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/products', [
            'name' => 'New Phone',
            'price' => 999.99,
            'category_id' => $category->id,
        ])->assertCreated();

        $versionAfterCreate = $cacheService->listCacheVersion();

        $this->assertGreaterThan($versionBeforeCreate, $versionAfterCreate);
        $this->getJson('/api/products?name=New')
            ->assertOk()
            ->assertJsonFragment(['name' => 'New Phone']);
    }

    public function test_products_list_cache_is_invalidated_after_product_update(): void
    {
        $category = Category::query()->create(['name' => 'Books']);
        $product = Product::query()->create([
            'name' => 'Old Title',
            'price' => 10,
            'category_id' => $category->id,
        ]);
        $cacheService = app(ProductCacheService::class);

        $this->getJson('/api/products?name=Old')->assertOk();
        $versionBefore = $cacheService->listCacheVersion();

        Sanctum::actingAs(User::factory()->create());

        $this->putJson('/api/products/'.$product->id, [
            'name' => 'Renamed Book',
            'price' => 12.5,
            'category_id' => $category->id,
        ])->assertOk();

        $this->assertGreaterThan($versionBefore, $cacheService->listCacheVersion());
        $this->getJson('/api/products?name=Renamed')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Renamed Book']);
    }

    public function test_products_list_cache_is_invalidated_after_product_delete(): void
    {
        $category = Category::query()->create(['name' => 'Sports']);
        $product = Product::query()->create([
            'name' => 'Ball To Remove',
            'price' => 5,
            'category_id' => $category->id,
        ]);
        $cacheService = app(ProductCacheService::class);

        $this->getJson('/api/products?name=Ball')->assertOk();
        $versionBefore = $cacheService->listCacheVersion();

        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson('/api/products/'.$product->id)->assertNoContent();

        $this->assertGreaterThan($versionBefore, $cacheService->listCacheVersion());
        $this->getJson('/api/products?name=Ball')
            ->assertOk()
            ->assertJsonMissing(['name' => 'Ball To Remove']);
    }

    public function test_identical_product_list_requests_use_cached_payload_until_invalidation(): void
    {
        $category = Category::query()->create(['name' => 'Cache']);
        Product::query()->create([
            'name' => 'Stable Item',
            'price' => 1,
            'category_id' => $category->id,
        ]);

        $first = $this->getJson('/api/products')->assertOk()->json();
        $second = $this->getJson('/api/products')->assertOk()->json();

        $this->assertSame($first, $second);
    }
}
