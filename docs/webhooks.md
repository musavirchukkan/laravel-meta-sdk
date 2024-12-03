# Webhook Integration Examples

## Webhook Handler
```php
namespace App\Http\Controllers;

use YourVendor\MetaSdk\Facades\Meta;

class MetaWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $this->verifyWebhook($request);
        
        $entry = $request->input('entry.0');
        $changes = $entry['changes'][0];

        match ($changes['value']['item']) {
            'leadgen' => $this->handleLeadgenEvent($changes),
            'ads_action' => $this->handleAdsEvent($changes),
            default => null
        };

        return response()->json(['success' => true]);
    }

    private function handleLeadgenEvent(array $changes)
    {
        $formId = $changes['value']['form_id'];
        $leadId = $changes['value']['leadgen_id'];
        
        ProcessNewLead::dispatch(
            Meta::leads()->getFormLeads($formId, $this->getToken())
        );
    }

    private function verifyWebhook(Request $request)
    {
        $signature = $request->header('x-hub-signature');
        $payload = $request->getContent();
        
        throw_unless(
            hash_equals(
                hash_hmac('sha256', $payload, config('meta.webhook_secret')),
                $signature
            ),
            WebhookException::class
        );
    }
}
```

## Routes Configuration
```php
Route::post('webhooks/meta', [MetaWebhookController::class, 'handle'])
    ->middleware(['api', 'meta.verify']);
```

## Middleware
```php
namespace App\Http\Middleware;

class VerifyMetaWebhook
{
    public function handle($request, $next)
    {
        if ($request->input('hub_mode') === 'subscribe') {
            return response(
                $request->input('hub_challenge')
            );
        }

        return $next($request);
    }
}
```

## Event Processor
```php
namespace App\Jobs;

class ProcessWebhookEvent implements ShouldQueue
{
    public function handle()
    {
        $changes = $this->event['changes'];
        
        MetaWebhookEvent::create([
            'type' => $changes['value']['item'],
            'object_id' => $changes['value']['id'],
            'data' => $changes
        ]);

        Meta::metrics()->recordMetric(
            "webhook.{$changes['value']['item']}", 
            1
        );
    }
}
```