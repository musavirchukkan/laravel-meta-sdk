<?php

namespace YourVendor\MetaSdk\Traits;

trait HandlesRetries
{
    protected int $maxRetries = 3;
    protected array $retryableStatusCodes = [408, 429, 500, 502, 503, 504];
    protected array $retryDelays = [1000, 2000, 4000]; // milliseconds

    protected function shouldRetry(\Exception $e, int $attempt): bool
    {
        if ($attempt >= $this->maxRetries) {
            return false;
        }

        if ($e instanceof \GuzzleHttp\Exception\RequestException) {
            $statusCode = $e->getResponse()?->getStatusCode();
            return in_array($statusCode, $this->retryableStatusCodes);
        }

        return false;
    }

    protected function wait(int $attempt): void
    {
        if (isset($this->retryDelays[$attempt - 1])) {
            usleep($this->retryDelays[$attempt - 1] * 1000);
        }
    }
}