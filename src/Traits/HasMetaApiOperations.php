<?php

namespace YourVendor\MetaSdk\Traits;

trait HasMetaApiOperations
{
    protected function getInsights(string $objectId, string $token, array $metrics = [], array $dateRange = [], string $level = 'campaign'): array
    {
        $params = [
            'access_token' => $token,
            'fields' => implode(',', $metrics),
            'level' => $level
        ];

        if (!empty($dateRange)) {
            $params['time_range'] = json_encode([
                'since' => $dateRange['since'] ?? date('Y-m-d', strtotime('-30 days')),
                'until' => $dateRange['until'] ?? date('Y-m-d')
            ]);
        }

        return $this->makeRequest('GET', $objectId . '/insights', $params, "get_{$level}_insights");
    }

    protected function getChildren(string $parentId, string $token, string $edge, array $fields): array
    {
        return $this->makeRequest('GET', $parentId . '/' . $edge, [
            'access_token' => $token,
            'fields' => implode(',', $fields)
        ], "get_{$edge}");
    }

    protected function getObject(string $objectId, string $token, array $fields): array
    {
        return $this->makeRequest('GET', $objectId, [
            'access_token' => $token,
            'fields' => implode(',', $fields)
        ], 'get_object');
    }
}