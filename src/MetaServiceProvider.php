<?php

namespace YourVendor\MetaSdk;

use Illuminate\Support\ServiceProvider;
use YourVendor\MetaSdk\Console\Commands\{MetricsReport, MetricsClear};
use YourVendor\MetaSdk\Contracts\{
    MetaAuthInterface,
    LeadServiceInterface,
    CampaignServiceInterface
};
use YourVendor\MetaSdk\Services\{
    MetaAuthService,
    LeadService,
    CampaignService,
    MetricsCollectorService
};

class MetaServiceProvider extends ServiceProvider
{
    protected $commands = [
        MetricsReport::class,
        MetricsClear::class
    ];

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/Config/meta.php', 'meta');

        $this->app->singleton(MetricsCollectorService::class);

        $this->app->bind(MetaAuthInterface::class, MetaAuthService::class);
        $this->app->bind(LeadServiceInterface::class, LeadService::class);
        $this->app->bind(CampaignServiceInterface::class, CampaignService::class);

        $this->app->singleton('meta', function ($app) {
            return new MetaManager($app);
        });

        $this->commands($this->commands);
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/Config/meta.php' => config_path('meta.php'),
        ], 'meta-config');

        $this->app['config']->set('logging.channels.meta', [
            'driver' => 'daily',
            'path' => storage_path('logs/meta.log'),
            'level' => 'debug',
            'days' => 14,
        ]);
    }
}