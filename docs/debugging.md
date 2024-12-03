# Debugging Tools

## Debug Mode
```php
// Enable debug mode in config/meta.php
'debug' => env('META_DEBUG', false),

// Detailed logging
Meta::setDebugMode(true)->leads()->getFormLeads($formId, $token);

// Debug output
[
    'request' => [
        'endpoint' => 'v18.0/123/leads',
        'params' => [...],
        'headers' => [...]
    ],
    'response' => [...],
    'metrics' => [...],
    'cache_hits' => 2,
    'duration_ms' => 145
]
```

## Request Tracking
```php
class RequestTracker
{
    public function track($callback)
    {
        $requestId = Str::uuid();
        
        try {
            $result = $callback();
            
            MetaRequestLog::create([
                'request_id' => $requestId,
                'duration' => $this->getDuration(),
                'success' => true
            ]);
            
            return $result;
        } catch (\Exception $e) {
            MetaRequestLog::create([
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
```

## Performance Monitoring
```php
Meta::metrics()->recordMetric('request_duration', function () use ($operation) {
    return [
        'latency' => $this->getLatency(),
        'memory' => memory_get_peak_usage(true),
        'cache_hits' => Cache::tags('meta')->getHits(),
    ];
});
```

## Testing Utilities
```php
class MetaTestCase extends TestCase
{
    protected function mockMetaResponse(string $endpoint, array $response)
    {
        $this->mock(MetaApiClient::class)
            ->shouldReceive('request')
            ->with($endpoint)
            ->andReturn($response);
    }
    
    protected function assertMetricLogged(string $metric)
    {
        $this->assertDatabaseHas('meta_metrics', [
            'name' => $metric,
            'created_at' => now()
        ]);
    }
}
```