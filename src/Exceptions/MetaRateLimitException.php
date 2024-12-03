<?php

namespace YourVendor\MetaSdk\Exceptions;

class MetaRateLimitException extends MetaApiException
{
    public function __construct(string $message, array $context = [])
    {
        parent::__construct($message, $context);
    }
}