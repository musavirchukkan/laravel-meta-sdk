<?php

namespace YourVendor\MetaSdk\Traits;

use Illuminate\Support\Facades\Cache;

trait CollectsMetrics
{
    protected string $metricsPrefix = 'meta_metrics:';
    
    protected function recordMetric(string $metricName, float|int $value): void
    {
        $key = $this->metricsPrefix . $metricName;
        $metrics = Cache::get($key, []);
        
        $metrics[] = [
            'timestamp' => now()->timestamp,
            'value' => $value
        ];
        
        Cache::put($key, array_slice($metrics, -100), now()->addDays(7));
    }

    protected function trackRequestDuration(string $operation, callable $callback)
    {
        $start = microtime(true);
        $result = $callback();
        $duration = microtime(true) - $start;
        
        $this->recordMetric("request_duration.{$operation}", $duration);
        $this->recordMetric('requests_total', 1);
        
        return $result;
    }
}