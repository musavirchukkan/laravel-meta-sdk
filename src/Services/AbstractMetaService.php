<?php

namespace YourVendor\MetaSdk\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use YourVendor\MetaSdk\Exceptions\MetaApiException;
use YourVendor\MetaSdk\Traits\{
    HasBatchRequests,
    HandlesRateLimiting,
    HandlesRetries,
    HandlesCaching,
    HandlesLogging,
    CollectsMetrics
};

abstract class AbstractMetaService
{
    use HasBatchRequests, HandlesRateLimiting, HandlesRetries, 
        HandlesCaching, HandlesLogging, CollectsMetrics;

    protected Client $client;
    protected array $config;
    protected int $batchLimit = 50;

    public function __construct()
    {
        $this->client = new Client();
        $this->config = config('meta');
    }

    protected function makeRequest(string $method, string $endpoint, array $params = [], string $operation = '', ?int $cacheMinutes = null): array
    {
        $this->logRequest($operation, $params);

        return $this->trackRequestDuration($operation, function () use ($method, $endpoint, $params, $operation, $cacheMinutes) {
            return $this->remember($endpoint, $params, function () use ($method, $endpoint, $params, $operation) {
                $attempt = 1;
                $lastException = null;

                do {
                    try {
                        $this->checkRateLimit($operation);
                        $this->recordMetric("rate_limit.attempt.{$operation}", $attempt);

                        $response = $this->client->request($method, $this->config['graph_url'] . $endpoint, [
                            'query' => array_merge($params, ['version' => $this->config['version']])
                        ]);

                        $data = json_decode($response->getBody(), true);
                        $headers = $response->getHeaders();

                        $this->handleApiResponse($headers);

                        if (isset($data['error'])) {
                            throw MetaApiException::fromResponse($data, $operation);
                        }

                        $this->logResponse($operation, $data);
                        $this->recordMetric("success.{$operation}", 1);
                        return $data;

                    } catch (\Exception $e) {
                        $lastException = $e;
                        $this->logError($operation, $e);
                        $this->recordMetric("error.{$operation}", 1);

                        if ($this->shouldRetry($e, $attempt)) {
                            $this->wait($attempt);
                            $attempt++;
                            continue;
                        }

                        throw $e;
                    }
                } while ($attempt <= $this->maxRetries);

                throw $lastException;
            }, $cacheMinutes);
        });
    }

    protected function chunkedBatchRequest(array $requests, string $token, ?int $cacheMinutes = null): array
    {
        return $this->trackRequestDuration('batch_request', function () use ($requests, $token, $cacheMinutes) {
            $chunks = array_chunk($requests, $this->batchLimit);
            $results = [];

            foreach ($chunks as $index => $chunk) {
                $results = array_merge(
                    $results,
                    $this->processBatchChunk($chunk, $token, $index)
                );
            }

            return $results;
        });
    }

    private function processBatchChunk(array $chunk, string $token, int $chunkIndex): array
    {
        $attempt = 1;
        do {
            try {
                $this->checkRateLimit('batch_request');
                $this->recordMetric("batch.chunk.{$chunkIndex}", count($chunk));
                
                return $this->batchRequest($chunk, $token);
            } catch (\Exception $e) {
                $this->logError('batch_request', $e);
                $this->recordMetric('batch.error', 1);

                if (!$this->shouldRetry($e, $attempt)) {
                    throw $e;
                }
                $this->wait($attempt);
                $attempt++;
            }
        } while ($attempt <= $this->maxRetries);

        throw new MetaApiException('Max retries exceeded for batch chunk');
    }
}