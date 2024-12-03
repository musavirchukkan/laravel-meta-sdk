<?php

namespace YourVendor\MetaSdk\Traits;

trait HasBatchRequests
{
    public function batchRequest(array $requests, string $token): array
    {
        $batch = array_map(function ($request) {
            return [
                'method' => $request['method'] ?? 'GET',
                'relative_url' => $request['endpoint'],
            ];
        }, $requests);

        return $this->makeRequest('POST', '', [
            'access_token' => $token,
            'batch' => json_encode($batch)
        ], 'batch_request');
    }
}