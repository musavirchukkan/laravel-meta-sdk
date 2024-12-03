<?php

namespace YourVendor\MetaSdk\Services;

use YourVendor\MetaSdk\Contracts\CampaignServiceInterface;
use YourVendor\MetaSdk\Traits\{HasMetaApiOperations, HandlesPagination};

class CampaignService extends AbstractMetaService implements CampaignServiceInterface
{
    use HasMetaApiOperations, HandlesPagination;

    protected array $defaultMetrics = [
        'impressions', 'clicks', 'spend', 'reach', 'cpm', 'cpc', 'ctr'
    ];

    protected array $cacheDuration = [
        'campaigns' => 30,    // 30 minutes
        'insights' => 15,     // 15 minutes
        'adsets' => 30,       // 30 minutes
        'ads' => 30           // 30 minutes
    ];

    public function getCampaigns(string $adAccountId, string $token, array $fields = [], int $limit = null): array
    {
        $defaultFields = ['id', 'name', 'objective', 'status', 'daily_budget', 'lifetime_budget'];
        return $this->getPagedResults('act_' . $adAccountId . '/campaigns', [
            'fields' => implode(',', empty($fields) ? $defaultFields : $fields)
        ], $token, $limit, $this->cacheDuration['campaigns']);
    }

    public function getCampaignInsights(string $campaignId, string $token, array $metrics = [], array $dateRange = []): array
    {
        return $this->getInsights($campaignId, $token, 
            empty($metrics) ? $this->defaultMetrics : $metrics, 
            $dateRange,
            'campaign',
            $this->cacheDuration['insights']
        );
    }

    public function getAdSets(string $campaignId, string $token, int $limit = null): array
    {
        return $this->getPagedResults($campaignId . '/adsets', [
            'fields' => 'id,name,status,daily_budget,targeting'
        ], $token, $limit, $this->cacheDuration['adsets']);
    }

    public function getAds(string $adSetId, string $token, int $limit = null): array
    {
        return $this->getPagedResults($adSetId . '/ads', [
            'fields' => 'id,name,status,creative'
        ], $token, $limit, $this->cacheDuration['ads']);
    }

    public function clearCampaignCache(string $campaignId): void
    {
        $this->forget($campaignId, []);
        $this->forget($campaignId . '/insights', []);
    }

    public function clearAdSetCache(string $adSetId): void
    {
        $this->forget($adSetId . '/ads', []);
    }
}