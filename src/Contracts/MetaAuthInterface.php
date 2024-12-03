<?php

namespace Musavirchukkan\LaravelMetaSdk\Contracts;

interface MetaAuthInterface
{
    public function getAuthorizationUrl(array $scopes = [], array $customData = []): string;
    public function handleCallback(string $code, ?string $state = null): array;
    public function exchangeLongLivedToken(string $shortLivedToken): string;
    public function validateToken(string $token): bool;
}