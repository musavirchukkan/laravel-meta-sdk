<?php

namespace YourVendor\MetaSdk\Traits;

use Illuminate\Support\Facades\Cache;
use YourVendor\MetaSdk\Exceptions\MetaRateLimitException;

trait HandlesRateLimiting
{
    protected int $maxAttempts = 200;
    protected int $decayMinutes = 1;
    protected string $cachePrefix = 'meta_api_rate_limit:';

    protected function checkRateLimit(string $identifier): void
    {
        $key = $this->cachePrefix . $identifier;
        $attempts = Cache::get($key, 0);

        if ($attempts >= $this->maxAttempts) {
            throw new MetaRateLimitException(
                "Rate limit exceeded for {$identifier}",
                ['max_attempts' => $this->maxAttempts, 'decay_minutes' => $this->decayMinutes]
            );
        }

        Cache::put($key, $attempts + 1, now()->addMinutes($this->decayMinutes));
    }

    protected function handleApiResponse(array $response): void
    {
        if (isset($response['x-business-use-case-usage'])) {
            foreach ($response['x-business-use-case-usage'] as $usage) {
                if ($usage['call_count'] >= $usage['total_capping']) {
                    throw new MetaRateLimitException(
                        "Business use case rate limit reached",
                        ['usage' => $usage]
                    );
                }
            }
        }
    }
}