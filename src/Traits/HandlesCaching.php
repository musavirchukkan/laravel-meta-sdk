<?php

namespace YourVendor\MetaSdk\Traits;

use Illuminate\Support\Facades\Cache;

trait HandlesCaching
{
    protected int $defaultCacheMinutes = 60;
    protected string $cachePrefix = 'meta_api_cache:';

    protected function getCacheKey(string $endpoint, array $params): string
    {
        return $this->cachePrefix . md5($endpoint . serialize($params));
    }

    protected function remember(string $endpoint, array $params, \Closure $callback, ?int $minutes = null): array
    {
        $key = $this->getCacheKey($endpoint, $params);
        $minutes = $minutes ?? $this->defaultCacheMinutes;

        return Cache::remember($key, now()->addMinutes($minutes), $callback);
    }

    protected function forget(string $endpoint, array $params): void
    {
        Cache::forget($this->getCacheKey($endpoint, $params));
    }
}