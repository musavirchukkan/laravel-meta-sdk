<?php

namespace YourVendor\MetaSdk;

use Illuminate\Contracts\Foundation\Application;
use YourVendor\MetaSdk\Contracts\{
    MetaAuthInterface,
    LeadServiceInterface,
    CampaignServiceInterface
};
use YourVendor\MetaSdk\Services\MetricsCollectorService;

class MetaManager
{
    protected $app;
    protected $services = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function auth(): MetaAuthInterface
    {
        return $this->resolve('auth', MetaAuthInterface::class);
    }

    public function leads(): LeadServiceInterface
    {
        return $this->resolve('leads', LeadServiceInterface::class);
    }

    public function campaigns(): CampaignServiceInterface
    {
        return $this->resolve('campaigns', CampaignServiceInterface::class);
    }

    public function metrics(): MetricsCollectorService
    {
        return $this->app->make(MetricsCollectorService::class);
    }

    protected function resolve(string $name, string $interface)
    {
        if (!isset($this->services[$name])) {
            $this->services[$name] = $this->app->make($interface);
        }

        return $this->services[$name];
    }
}