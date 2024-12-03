<?php

namespace YourVendor\MetaSdk\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Orchestra\Testbench\TestCase as BaseTestCase;
use YourVendor\MetaSdk\MetaServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [MetaServiceProvider::class];
    }

    protected function mockHttpResponse($json)
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode($json))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->app->instance(Client::class, $client);
    }
}