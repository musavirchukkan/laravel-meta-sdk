# Implementation Examples

## Lead Form Integration
```php
namespace App\Services;

use YourVendor\MetaSdk\Facades\Meta;

class LeadService
{
    public function syncLeads(string $formId, string $pageToken)
    {
        try {
            $leads = Meta::leads()->getFormLeads($formId, $pageToken);
            
            foreach ($leads['data'] as $lead) {
                $this->processLead($lead);
            }
            
            return [
                'success' => true,
                'count' => count($leads['data'])
            ];
        } catch (\Exception $e) {
            Log::error('Lead sync failed', [
                'form_id' => $formId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function processLead(array $lead)
    {
        $leadData = [];
        foreach ($lead['field_data'] as $field) {
            $leadData[$field['name']] = $field['values'][0] ?? null;
        }
        
        return Lead::create([
            'external_id' => $lead['id'],
            'form_id' => $lead['form_id'],
            'email' => $leadData['email'] ?? null,
            'name' => $leadData['full_name'] ?? null,
            'phone' => $leadData['phone'] ?? null,
            'created_at' => $lead['created_time']
        ]);
    }
}
```

## Campaign Performance Monitor
```php
namespace App\Services;

use YourVendor\MetaSdk\Facades\Meta;

class CampaignMonitor
{
    public function getDailyPerformance(string $campaignId, string $token)
    {
        $insights = Meta::campaigns()->getCampaignInsights(
            $campaignId, 
            $token,
            ['impressions', 'clicks', 'spend', 'leads'],
            ['since' => now()->subDays(30)->format('Y-m-d')]
        );

        return [
            'today' => $this->calculateMetrics($insights['data'][0] ?? []),
            'metrics' => Meta::metrics()->getMetricsSummary('campaigns')
        ];
    }

    private function calculateMetrics(array $data): array
    {
        return [
            'ctr' => ($data['clicks'] / $data['impressions']) * 100,
            'cpl' => $data['spend'] / ($data['leads'] ?: 1),
            'spend' => $data['spend']
        ];
    }
}
```

## Artisan Command
```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use YourVendor\MetaSdk\Facades\Meta;

class SyncMetaLeads extends Command
{
    protected $signature = 'meta:sync-leads {form_id}';
    
    public function handle()
    {
        $formId = $this->argument('form_id');
        $token = config('services.meta.token');

        try {
            $leads = Meta::leads()->getFormLeads($formId, $token);
            
            $bar = $this->output->createProgressBar(count($leads['data']));
            
            foreach ($leads['data'] as $lead) {
                ProcessLead::dispatch($lead);
                $bar->advance();
            }
            
            $bar->finish();
            
            $metrics = Meta::metrics()->getMetricsSummary('leads');
            $this->info("\nSync complete: {$metrics['success']['count']} leads processed");
            
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
```