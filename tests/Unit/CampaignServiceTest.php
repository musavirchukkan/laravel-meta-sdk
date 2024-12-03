<?php

namespace YourVendor\MetaSdk\Tests\Unit;

use YourVendor\MetaSdk\Facades\Meta;
use YourVendor\MetaSdk\Tests\TestCase;
use YourVendor\MetaSdk\Tests\Mocks\MetaMockResponses;

class CampaignServiceTest extends TestCase
{
    public function test_can_get_campaigns()
    {
        $this->mockHttpResponse(MetaMockResponses::campaigns());
        
        $campaigns = Meta::campaigns()->getCampaigns('123456789', 'test_token');
        
        $this->assertIsArray($campaigns);
        $this->assertArrayHasKey('data', $campaigns);
        $this->assertEquals('Test Campaign', $campaigns['data'][0]['name']);
    }

    public function test_can_get_campaign_insights()
    {
        $this->mockHttpResponse(MetaMockResponses::insights());
        
        $insights = Meta::campaigns()->getCampaignInsights('123456789', 'test_token');
        
        $this->assertIsArray($insights);
        $this->assertArrayHasKey('data', $insights);
        $this->assertEquals('1000', $insights['data'][0]['impressions']);
    }
}