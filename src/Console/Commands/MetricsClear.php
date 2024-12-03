<?php

namespace YourVendor\MetaSdk\Console\Commands;

use Illuminate\Console\Command;
use YourVendor\MetaSdk\Services\MetricsCollectorService;

class MetricsClear extends Command
{
    protected $signature = 'meta:metrics:clear
                          {operation? : Specific operation metrics to clear}';

    protected $description = 'Clear collected Meta API metrics';

    public function __construct(private MetricsCollectorService $metrics)
    {
        parent::__construct();
    }

    public function handle()
    {
        $operation = $this->argument('operation');
        $this->metrics->clearMetrics($operation);
        $this->info($operation 
            ? "Cleared metrics for operation: {$operation}"
            : 'Cleared all metrics');
    }
}