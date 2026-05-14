<?php

namespace App\Services;

use App\Http\Requests\IndexProductRequest;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductService
{
    public function __construct(protected ProductCacheService $cacheService) {}

    public function paginateList(IndexProductRequest $request): array
    {
        return $this->cacheService->rememberList(
            $request->query(),
            fn (): array => $this->buildFilteredQuery($request)->paginate(15)->toArray()
        );
    }

    public function create(array $data): Product
    {
        $product = Product::query()->create($data);
        $this->cacheService->invalidateList();

        return $product->load('category');
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        $this->cacheService->invalidateList();

        return $product->fresh()->load('category');
    }

    public function delete(Product $product): void
    {
        $product->delete();
        $this->cacheService->invalidateList();
    }

    /**
     * @return Builder<Product>
     */
    private function buildFilteredQuery(IndexProductRequest $request): Builder
    {
        $sortBy = $request->input('sort_by', 'created_at');
        if (!in_array($sortBy, ['price', 'created_at'], true)) {
            $sortBy = 'created_at';
        }

        $sortDirection = strtolower((string) $request->input('sort_direction', 'desc'));
        if (!in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        /** @var Builder<Product> $query */
        $query = Product::query()->with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', (float) $request->input('price_min'));
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', (float) $request->input('price_max'));
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->input('name').'%');
        }

        return $query->orderBy($sortBy, $sortDirection);
    }
}
