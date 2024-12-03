<?php

namespace YourVendor\MetaSdk\Traits;

trait HandlesPagination
{
    protected function getPaginatedResults(string $endpoint, array $params, string $token): array
    {
        $results = [];
        $nextPage = null;
        
        do {
            $response = $this->makeRequest('GET', $endpoint, array_merge(
                $params,
                ['access_token' => $token],
                $nextPage ? ['after' => $nextPage] : []
            ));

            if (isset($response['data'])) {
                $results = array_merge($results, $response['data']);
            }

            $nextPage = $response['paging']['cursors']['after'] ?? null;
        } while ($nextPage);

        return ['data' => $results];
    }

    protected function getPagedResults(string $endpoint, array $params, string $token, int $limit = null): array
    {
        if ($limit) {
            $params['limit'] = $limit;
        }

        return $this->makeRequest('GET', $endpoint, array_merge(
            $params,
            ['access_token' => $token]
        ));
    }
}