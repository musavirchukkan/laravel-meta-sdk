# Testing Features

## Mock Responses
```php
class LeadTest extends TestCase
{
    public function test_get_leads()
    {
        $this->mockMetaResponse([
            'data' => [
                ['id' => '123', 'email' => 'test@example.com']
            ]
        ]);

        $leads = Meta::leads()->getFormLeads('form_id', 'token');
        $this->assertCount(1, $leads['data']);
    }
}
```

## Test Traits
```php
trait MetaTestHelpers
{
    protected function mockAuth()
    {
        return $this->mock(MetaAuthService::class)
            ->shouldReceive('validateToken')
            ->andReturn(true);
    }

    protected function assertRateLimitRespected()
    {
        $metrics = Meta::metrics()->getMetricsSummary('rate_limit');
        $this->assertLessThan(200, $metrics['requests']['count']);
    }
}
```

## Feature Tests
```php
class WebhookTest extends TestCase
{
    public function test_webhook_handling()
    {
        Event::fake();
        
        $payload = $this->getTestWebhookPayload();
        
        $response = $this->postJson('/webhooks/meta', $payload, [
            'X-Hub-Signature' => $this->generateSignature($payload)
        ]);
        
        $response->assertOk();
        Event::assertDispatched(LeadReceived::class);
    }
}
```

## Integration Tests
```php
class CampaignIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('meta.test_mode', true);
    }

    public function test_campaign_sync()
    {
        $result = Meta::campaigns()
            ->getCampaigns('test_account', 'test_token');
            
        $this->assertArrayHasKey('data', $result);
        $this->assertMetricLogged('campaign_sync');
    }
}
```