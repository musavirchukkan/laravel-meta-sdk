<?php

namespace YourVendor\MetaSdk\Facades;

use Illuminate\Support\Facades\Facade;

class Meta extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'meta';
    }

    public static function metrics(): \YourVendor\MetaSdk\Services\MetricsCollectorService
    {
        return app(\YourVendor\MetaSdk\Services\MetricsCollectorService::class);
    }
}