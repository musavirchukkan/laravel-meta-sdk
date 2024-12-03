# Advanced Webhook Features

## Real-time Monitoring
```php
namespace App\Services;

class WebhookMonitor
{
    public function monitor()
    {
        return [
            'metrics' => Meta::metrics()->getMetricsSummary('webhook'),
            'health' => [
                'success_rate' => $this->getSuccessRate(),
                'avg_response_time' => $this->getResponseTime(),
                'errors' => $this->getRecentErrors()
            ]
        ];
    }

    private function getSuccessRate(): float
    {
        return Cache::remember('webhook_success_rate', 300, function () {
            $metrics = Meta::metrics()->getMetricsSummary('webhook', 60);
            return ($metrics['success']['count'] / max(1, $metrics['total']['count'])) * 100;
        });
    }
}
```

## Retry Logic
```php
namespace App\Jobs;

class RetryFailedWebhooks implements ShouldQueue
{
    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    public function handle()
    {
        $events = FailedWebhookEvent::pending()->get();
        
        foreach ($events as $event) {
            try {
                ProcessWebhookEvent::dispatch($event)
                    ->onQueue('webhooks-retry');
                    
                $event->markAsProcessed();
                
            } catch (\Exception $e) {
                $event->incrementAttempts();
                $this->notifyIfMaxAttemptsReached($event);
            }
        }
    }
}
```

## Rate Limiting
```php
namespace App\Http\Middleware;

class WebhookRateLimit
{
    public function handle($request, Closure $next)
    {
        return RateLimiter::attempt(
            'webhooks:'.$request->ip(),
            300, // Allow 300 webhook calls
            function() use ($next, $request) {
                return $next($request);
            },
            60 // Per minute
        );
    }
}
```

## Webhook Subscription Management
```php
namespace App\Services;

class WebhookManager
{
    public function subscribe(array $fields)
    {
        return Meta::auth()->makeRequest('POST', '/app/subscriptions', [
            'object' => 'page',
            'callback_url' => config('app.url').'/webhooks/meta',
            'fields' => $fields,
            'include_values' => true
        ]);
    }

    public function listSubscriptions()
    {
        return Meta::auth()->makeRequest('GET', '/app/subscriptions');
    }
}
```

## Testing Webhooks
```php
namespace Tests\Feature;

class WebhookTest extends TestCase
{
    public function test_validates_webhook_signature()
    {
        $payload = ['entry' => [['changes' => []]]];
        $signature = hash_hmac('sha256', json_encode($payload), config('meta.webhook_secret'));

        $response = $this->postJson('/webhooks/meta', $payload, [
            'x-hub-signature' => $signature
        ]);

        $response->assertSuccessful();
    }
}
```