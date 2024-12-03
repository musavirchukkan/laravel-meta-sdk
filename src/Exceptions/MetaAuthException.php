<?php

namespace Musavirchukkan\LaravelMetaSdk\Exceptions;

class MetaAuthException extends \Exception
{
    protected $errorData;

    public function __construct(string $message, array $errorData = [], int $code = 0)
    {
        $this->errorData = $errorData;
        parent::__construct($message, $code);
    }

    public function getErrorData(): array
    {
        return $this->errorData;
    }
}