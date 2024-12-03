# Integration Examples

## Laravel Queue Integration
```php
namespace App\Jobs;

use YourVendor\MetaSdk\Facades\Meta;

class ProcessLeadForms implements ShouldQueue
{
    public function handle()
    {
        Redis::throttle('meta_api')
            ->allow(200)
            ->every(60)
            ->then(function () {
                $token = cache('meta_token');
                $forms = Meta::leads()->getLeadForms($pageId, $token);
                
                foreach ($forms['data'] as $form) {
                    ProcessFormLeads::dispatch($form['id']);
                }
            });
    }
}
```

## Laravel Scheduler
```php
namespace App\Console;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('meta:metrics')
            ->hourly()
            ->appendOutputTo(storage_path('logs/meta-metrics.log'));
            
        $schedule->job(new SyncCampaignInsights)
            ->dailyAt('00:00')
            ->withoutOverlapping();
    }
}
```

## Laravel Events
```php
namespace App\Listeners;

class MetaLeadReceived
{
    public function handle(LeadReceived $event)
    {
        event(new NewLead($event->lead));
        
        Meta::metrics()
            ->recordMetric('leads_processed', 1);
            
        if ($event->lead['score'] > 80) {
            Notification::send(
                $admins,
                new HighValueLead($event->lead)
            );
        }
    }
}
```

## Laravel Cache Integration
```php
namespace App\Services;

class MetaCacheManager
{
    public function warmCache(): void
    {
        Cache::tags(['meta'])
            ->remember('active_campaigns', now()->addHour(), function () {
                return Meta::campaigns()
                    ->getCampaigns($adAccountId, $token, ['status' => 'ACTIVE']);
            });
    }

    public function flushMetaCache(): void
    {
        Cache::tags(['meta'])->flush();
        Meta::metrics()->clearMetrics();
    }
}
```

## Laravel Notifications
```php
namespace App\Notifications;

class CampaignPerformanceAlert extends Notification
{
    public function toArray($notifiable): array
    {
        $metrics = Meta::metrics()
            ->getMetricsSummary('campaigns', 60);
            
        return [
            'type' => 'campaign_alert',
            'metrics' => $metrics,
            'threshold_exceeded' => true
        ];
    }
}
```