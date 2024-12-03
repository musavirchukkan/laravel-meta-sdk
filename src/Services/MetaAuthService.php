<?php

namespace Musavirchukkan\LaravelMetaSdk\Services;

use GuzzleHttp\Client;
use Musavirchukkan\LaravelMetaSdk\Contracts\MetaAuthInterface;
use Musavirchukkan\LaravelMetaSdk\Exceptions\MetaAuthException;

class MetaAuthService implements MetaAuthInterface
{
    protected Client $client;
    protected array $config;

    public function __construct()
    {
        $this->client = new Client();
        $this->config = config('meta');
    }

    public function getAuthorizationUrl(array $scopes = [], array $customData = []): string
    {
        $defaultScopes = [
            'email',
            'leads_retrieval',
            'pages_manage_metadata',
            'pages_show_list',
            'pages_manage_ads',
            'business_management',
            'pages_read_engagement',
            'ads_management'
        ];

        $scopes = array_merge($defaultScopes, $scopes);
        $state = urlencode(json_encode($customData));

        return sprintf(
            '%sv%s/dialog/oauth?client_id=%s&redirect_uri=%s&scope=%s&state=%s',
            $this->config['graph_url'],
            $this->config['version'],
            $this->config['client_id'],
            urlencode($this->config['redirect_uri']),
            implode(',', $scopes),
            $state
        );
    }

    public function handleCallback(string $code, ?string $state = null): array
    {
        try {
            $shortLivedToken = $this->exchangeCode($code);
            $longLivedToken = $this->exchangeLongLivedToken($shortLivedToken);
            $userData = $this->getUserData($longLivedToken);
            $pages = $this->getPages($longLivedToken);

            return [
                'access_token' => $longLivedToken,
                'user_id' => $userData['id'] ?? null,
                'pages' => $pages['data'] ?? [],
                'state' => $state ? json_decode(urldecode($state), true) : null
            ];
        } catch (\Exception $e) {
            throw new MetaAuthException('Failed to handle callback', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }

    public function exchangeLongLivedToken(string $shortLivedToken): string
    {
        try {
            $response = $this->client->get($this->config['graph_url'] . 'oauth/access_token', [
                'query' => [
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => $this->config['client_id'],
                    'client_secret' => $this->config['client_secret'],
                    'fb_exchange_token' => $shortLivedToken
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['access_token'];
        } catch (\Exception $e) {
            throw new MetaAuthException('Failed to exchange long-lived token', [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function validateToken(string $token): bool
    {
        try {
            $response = $this->client->get($this->config['graph_url'] . 'debug_token', [
                'query' => [
                    'input_token' => $token,
                    'access_token' => $this->config['client_id'] . '|' . $this->config['client_secret']
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['data']['is_valid'] ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function exchangeCode(string $code): string
    {
        $response = $this->client->get($this->config['graph_url'] . 'oauth/access_token', [
            'query' => [
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'redirect_uri' => $this->config['redirect_uri'],
                'code' => $code
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['access_token'];
    }

    protected function getUserData(string $token): array
    {
        $response = $this->client->get($this->config['graph_url'] . 'me', [
            'query' => ['access_token' => $token]
        ]);

        return json_decode($response->getBody(), true);
    }

    protected function getPages(string $token): array
    {
        $response = $this->client->get($this->config['graph_url'] . 'me/accounts', [
            'query' => ['access_token' => $token]
        ]);

        return json_decode($response->getBody(), true);
    }
}