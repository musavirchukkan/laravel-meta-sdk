<?php

namespace YourVendor\MetaSdk\Traits;

use Illuminate\Support\Facades\Log;

trait HandlesLogging
{
    protected function logRequest(string $operation, array $params): void
    {
        Log::channel('meta')->debug('Meta API Request', [
            'operation' => $operation,
            'params' => array_diff_key($params, ['access_token' => '']),
        ]);
    }

    protected function logResponse(string $operation, array $response): void
    {
        Log::channel('meta')->debug('Meta API Response', [
            'operation' => $operation,
            'status' => 'success',
            'data_count' => count($response['data'] ?? [])
        ]);
    }

    protected function logError(string $operation, \Exception $e): void
    {
        Log::channel('meta')->error('Meta API Error', [
            'operation' => $operation,
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}