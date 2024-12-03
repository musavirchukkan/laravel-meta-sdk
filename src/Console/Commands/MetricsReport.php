<?php

namespace YourVendor\MetaSdk\Console\Commands;

use Illuminate\Console\Command;
use YourVendor\MetaSdk\Services\MetricsCollectorService;

class MetricsReport extends Command
{
    protected $signature = 'meta:metrics 
                          {operation? : The operation to report metrics for}
                          {--minutes=60 : Time window in minutes}
                          {--format=table : Output format (table/json)}';

    protected $description = 'Generate Meta API metrics report';

    protected MetricsCollectorService $metrics;

    public function __construct(MetricsCollectorService $metrics)
    {
        parent::__construct();
        $this->metrics = $metrics;
    }

    public function handle()
    {
        $operation = $this->argument('operation');
        $minutes = $this->option('minutes');
        
        $summary = $operation 
            ? $this->metrics->getMetricsSummary($operation, $minutes)
            : $this->getAllMetrics($minutes);

        $this->option('format') === 'json'
            ? $this->output->writeln(json_encode($summary, JSON_PRETTY_PRINT))
            : $this->displayTable($summary);
    }

    private function getAllMetrics(int $minutes): array
    {
        return [
            'auth' => $this->metrics->getMetricsSummary('auth', $minutes),
            'leads' => $this->metrics->getMetricsSummary('leads', $minutes),
            'campaigns' => $this->metrics->getMetricsSummary('campaigns', $minutes)
        ];
    }

    private function displayTable(array $summary): void
    {
        foreach ($summary as $operation => $metrics) {
            $this->info("\n{$operation} Metrics:");
            $rows = [];
            foreach ($metrics as $type => $stats) {
                $rows[] = [$type, $stats['count'], $stats['avg'], $stats['min'], $stats['max']];
            }
            
            $this->table(
                ['Metric', 'Count', 'Average', 'Min', 'Max'],
                $rows
            );
        }
    }
}