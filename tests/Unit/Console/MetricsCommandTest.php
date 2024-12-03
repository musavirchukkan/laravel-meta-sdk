<?php

namespace YourVendor\MetaSdk\Tests\Unit\Console;

use YourVendor\MetaSdk\Tests\TestCase;
use YourVendor\MetaSdk\Services\MetricsCollectorService;
use YourVendor\MetaSdk\Facades\Meta;

class MetricsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->metrics = app(MetricsCollectorService::class);
    }

    public function test_metrics_report_command()
    {
        Meta::leads()->getLeadForms('123', 'test-token');

        $this->artisan('meta:metrics')
            ->expectsTable(
                ['Metric', 'Count', 'Average', 'Min', 'Max'],
                [
                    ['request_duration', 1, $this->anything(), $this->anything(), $this->anything()],
                    ['success', 1, 1, 1, 1],
                ]
            )
            ->assertSuccessful();
    }

    public function test_metrics_report_json_format()
    {
        Meta::leads()->getLeadForms('123', 'test-token');

        $this->artisan('meta:metrics', ['--format' => 'json'])
            ->assertSuccessful()
            ->expectsOutput($this->callback(function ($output) {
                $data = json_decode($output, true);
                return isset($data['leads']['request_duration']);
            }));
    }

    public function test_metrics_clear_command()
    {
        Meta::leads()->getLeadForms('123', 'test-token');

        $this->artisan('meta:metrics:clear')
            ->expectsOutput('Cleared all metrics')
            ->assertSuccessful();

        $summary = $this->metrics->getMetricsSummary('leads');
        $this->assertEquals(0, $summary['request_duration']['count']);
    }

    public function test_metrics_clear_specific_operation()
    {
        Meta::leads()->getLeadForms('123', 'test-token');
        Meta::campaigns()->getCampaigns('123', 'test-token');

        $this->artisan('meta:metrics:clear', ['operation' => 'leads'])
            ->expectsOutput('Cleared metrics for operation: leads')
            ->assertSuccessful();

        $leadsSummary = $this->metrics->getMetricsSummary('leads');
        $campaignsSummary = $this->metrics->getMetricsSummary('campaigns');

        $this->assertEquals(0, $leadsSummary['request_duration']['count']);
        $this->assertGreaterThan(0, $campaignsSummary['request_duration']['count']);
    }
}