# Advanced Features

## Batch Operations
```php
use YourVendor\MetaSdk\Facades\Meta;

// Batch requests
$requests = [
    ['endpoint' => 'form_id_1/leads'],
    ['endpoint' => 'form_id_2/leads']
];
$results = Meta::leads()->batchRequest($requests, $token);
```

## Rate Limiting
The SDK implements automatic rate limiting:
- 200 requests per hour per endpoint
- Automatic retry with exponential backoff
- Rate limit exceptions handled gracefully

## Caching
```php
// Custom cache duration
Meta::leads()->getFormLeads($formId, $token, null, 120); // 2 hours

// Clear cache
Meta::leads()->clearLeadsCache($formId);
```

## Metrics Collection
```php
// Get operation metrics
$metrics = Meta::metrics()->getMetricsSummary('leads', 60);

// Structure
[
    'request_duration' => [
        'count' => int,
        'avg' => float,
        'min' => float,
        'max' => float
    ],
    'success' => [...],
    'error' => [...],
]
```

## Error Handling
```php
use YourVendor\MetaSdk\Exceptions\{
    MetaApiException,
    MetaAuthException,
    MetaRateLimitException
};

try {
    $leads = Meta::leads()->getFormLeads($formId, $token);
} catch (MetaRateLimitException $e) {
    // Rate limit exceeded
} catch (MetaApiException $e) {
    // API error with context
    $context = $e->getContext();
}
```

## Logging
Logs are stored in `storage/logs/meta.log`:
- Request/response details
- Rate limiting events
- Errors with stack traces
- Performance metrics

## Testing
```php
// Mock responses
$this->mockHttpResponse([
    'data' => [['id' => '123', 'name' => 'Test Form']]
]);

// Assert metrics
$this->assertMetricsCounted('leads', 1);
```

## Command Line
```bash
# Metrics report
php artisan meta:metrics --minutes=120 --format=json

# Clear specific metrics
php artisan meta:metrics:clear leads
```