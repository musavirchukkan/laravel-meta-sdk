<?php

namespace YourVendor\MetaSdk\Contracts;

interface CampaignServiceInterface
{
    public function getCampaigns(string $adAccountId, string $token, array $fields = []): array;
    public function getCampaignInsights(string $campaignId, string $token, array $metrics = [], array $dateRange = []): array;
    public function getAdSets(string $campaignId, string $token): array;
    public function getAds(string $adSetId, string $token): array;
}