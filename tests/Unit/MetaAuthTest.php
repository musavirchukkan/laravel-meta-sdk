<?php

namespace YourVendor\MetaSdk\Tests\Unit;

use Orchestra\Testbench\TestCase;
use YourVendor\MetaSdk\Facades\Meta;
use YourVendor\MetaSdk\MetaServiceProvider;

class MetaAuthTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [MetaServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('meta.client_id', 'test-client-id');
        $app['config']->set('meta.client_secret', 'test-client-secret');
        $app['config']->set('meta.redirect_uri', 'http://localhost/callback');
    }

    public function test_can_get_authorization_url()
    {
        $url = Meta::getAuthorizationUrl();
        $this->assertStringContainsString('test-client-id', $url);
        $this->assertStringContainsString('http://localhost/callback', $url);
    }
}