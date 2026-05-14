<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class ProductCacheService
{
    private const string LIST_VERSION_KEY = 'products:list:version';

    public function rememberList(array $filters, Closure $resolver, int $ttlSeconds = 300): array
    {
        $version = (int) Cache::get(self::LIST_VERSION_KEY, 1);
        $key = $this->buildListCacheKey($filters, $version);

        return Cache::remember($key, $ttlSeconds, fn (): array => $resolver());
    }

    public function invalidateList(): void
    {
        if (! Cache::has(self::LIST_VERSION_KEY)) {
            Cache::forever(self::LIST_VERSION_KEY, 1);
        }

        Cache::increment(self::LIST_VERSION_KEY);
    }

    public function listCacheVersion(): int
    {
        return (int) Cache::get(self::LIST_VERSION_KEY, 1);
    }

    private function buildListCacheKey(array $filters, int $version): string
    {
        ksort($filters);

        return 'products:list:v'.$version.':'.md5(json_encode($filters));
    }
}
