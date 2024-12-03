# Error Handling

## Exception Types
```php
use YourVendor\MetaSdk\Exceptions\{
    MetaApiException,
    MetaAuthException,
    MetaRateLimitException
};

try {
    $result = Meta::leads()->getFormLeads($formId, $token);
} catch (MetaAuthException $e) {
    Log::error('Auth failed', ['context' => $e->getContext()]);
} catch (MetaRateLimitException $e) {
    // Retry after delay
    rescue(fn() => RetryJob::dispatch()->delay(now()->addMinutes(5)));
} catch (MetaApiException $e) {
    $this->handleApiError($e);
}
```

## Global Handler
```php
namespace App\Exceptions;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (MetaApiException $e) {
            Notification::route('slack', config('logging.slack_webhook_url'))
                ->notify(new MetaApiErrorNotification($e));
        });

        $this->renderable(function (MetaApiException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'context' => $e->getContext()
            ], 500);
        });
    }
}
```

## Retry Strategy
```php
namespace App\Services;

class MetaRetryStrategy
{
    public function execute(callable $callback, int $maxAttempts = 3)
    {
        return retry($maxAttempts, $callback, function ($e) {
            return $this->isRetryable($e);
        }, function ($attempt) {
            return $this->getDelay($attempt);
        });
    }

    private function isRetryable(\Exception $e): bool
    {
        return $e instanceof MetaRateLimitException
            || ($e instanceof MetaApiException && in_array($e->getCode(), [500, 503]));
    }

    private function getDelay(int $attempt): int
    {
        return [5, 15, 30][$attempt - 1] ?? 60;
    }
}
```

## Error Monitoring
```php
$metrics = Meta::metrics()->getMetricsSummary('errors', 1440); // 24 hours

if ($metrics['error']['count'] > 100) {
    Notification::send(
        $devTeam,
        new HighErrorRateAlert($metrics)
    );
}
```