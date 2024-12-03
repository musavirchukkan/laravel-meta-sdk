<?php

namespace YourVendor\MetaSdk\Tests\Unit;

use YourVendor\MetaSdk\Facades\Meta;
use YourVendor\MetaSdk\Tests\TestCase;
use YourVendor\MetaSdk\Tests\Mocks\MetaMockResponses;

class LeadServiceTest extends TestCase
{
    public function test_can_get_lead_forms()
    {
        $this->mockHttpResponse(MetaMockResponses::leadForms());
        
        $forms = Meta::leads()->getLeadForms('123456789', 'test_token');
        
        $this->assertIsArray($forms);
        $this->assertArrayHasKey('data', $forms);
        $this->assertEquals('Test Form', $forms['data'][0]['name']);
    }

    public function test_can_get_form_leads()
    {
        $this->mockHttpResponse(MetaMockResponses::formLeads());
        
        $leads = Meta::leads()->getFormLeads('123456789', 'test_token');
        
        $this->assertIsArray($leads);
        $this->assertArrayHasKey('data', $leads);
        $this->assertEquals('test@example.com', $leads['data'][0]['field_data'][0]['values'][0]);
    }
}