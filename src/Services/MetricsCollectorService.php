<?php

namespace YourVendor\MetaSdk\Services;

use Illuminate\Support\Facades\Cache;

class MetricsCollectorService
{
    protected string $metricsPrefix = 'meta_metrics:';
    protected array $metricTypes = ['request_duration', 'rate_limit', 'success', 'error', 'batch'];

    public function getMetricsSummary(string $operation, int $minutes = 60): array
    {
        $summary = [];
        foreach ($this->metricTypes as $type) {
            $metrics = $this->getMetrics("{$type}.{$operation}", $minutes);
            $summary[$type] = $this->calculateStats($metrics);
        }
        return $summary;
    }

    protected function getMetrics(string $metricName, int $minutes): array
    {
        $key = $this->metricsPrefix . $metricName;
        $metrics = Cache::get($key, []);
        $cutoff = now()->subMinutes($minutes)->timestamp;

        return array_filter($metrics, fn($metric) => $metric['timestamp'] >= $cutoff);
    }

    protected function calculateStats(array $metrics): array
    {
        if (empty($metrics)) {
            return ['count' => 0, 'avg' => 0, 'min' => 0, 'max' => 0];
        }

        $values = array_column($metrics, 'value');
        return [
            'count' => count($values),
            'avg' => array_sum($values) / count($values),
            'min' => min($values),
            'max' => max($values),
        ];
    }

    public function clearMetrics(): void
    {
        $keys = Cache::get($this->metricsPrefix . '*');
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}