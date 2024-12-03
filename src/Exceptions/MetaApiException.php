<?php

namespace YourVendor\MetaSdk\Exceptions;

use Exception;

class MetaApiException extends Exception
{
    protected array $context;
    protected ?array $metaError;

    public function __construct(string $message, array $context = [], array $metaError = null)
    {
        $this->context = $context;
        $this->metaError = $metaError;
        parent::__construct($message);
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getMetaError(): ?array
    {
        return $this->metaError;
    }

    public static function fromResponse(array $response, string $operation): self
    {
        $error = $response['error'] ?? [];
        return new self(
            $error['message'] ?? 'Unknown Meta API error',
            ['operation' => $operation],
            $error
        );
    }
}