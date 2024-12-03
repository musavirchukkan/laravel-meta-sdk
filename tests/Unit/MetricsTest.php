<?php

namespace YourVendor\MetaSdk\Tests\Unit;

use YourVendor\MetaSdk\Tests\TestCase;
use YourVendor\MetaSdk\Services\MetricsCollectorService;
use YourVendor\MetaSdk\Facades\Meta;

class MetricsTest extends TestCase
{
    protected MetricsCollectorService $metrics;

    protected function setUp(): void
    {
        parent::setUp();
        $this->metrics = app(MetricsCollectorService::class);
    }

    public function test_can_collect_metrics()
    {
        Meta::leads()->getLeadForms('123', 'test-token');
        
        $summary = $this->metrics->getMetricsSummary('leads');
        
        $this->assertArrayHasKey('request_duration', $summary);
        $this->assertArrayHasKey('success', $summary);
    }

    public function test_can_clear_metrics()
    {
        Meta::leads()->getLeadForms('123', 'test-token');
        
        $this->metrics->clearMetrics();
        $summary = $this->metrics->getMetricsSummary('leads');
        
        $this->assertEquals(0, $summary['request_duration']['count']);
    }

    public function test_metrics_time_window()
    {
        Meta::leads()->getLeadForms('123', 'test-token');
        
        $summary = $this->metrics->getMetricsSummary('leads', 1); // 1 minute
        $this->assertGreaterThan(0, $summary['request_duration']['count']);
        
        $oldSummary = $this->metrics->getMetricsSummary('leads', -1); // Past window
        $this->assertEquals(0, $oldSummary['request_duration']['count']);
    }
}