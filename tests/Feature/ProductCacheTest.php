<?php

namespace Tests\Feature;

use App\Models\Category;
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
}
