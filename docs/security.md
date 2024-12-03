# Security Best Practices

## Token Management
```php
namespace App\Services;

class MetaTokenManager
{
    public function rotateToken(string $oldToken): string
    {
        return Cache::remember('meta_token', now()->addDays(60), function() use ($oldToken) {
            $response = Meta::auth()->exchangeLongLivedToken($oldToken);
            $this->logTokenRotation();
            return $response['access_token'];
        });
    }
}
```

## Access Control
```php
namespace App\Policies;

class MetaResourcePolicy
{
    public function access(User $user, string $resource): bool
    {
        return match($resource) {
            'leads' => $user->hasPermission('meta.leads.read'),
            'campaigns' => $user->hasPermission('meta.campaigns.manage'),
            default => false
        };
    }
}
```

## Encryption
```php
class MetaCredentials
{
    public function storeCredentials(array $credentials): void
    {
        foreach($credentials as $key => $value) {
            Cache::put(
                "meta.{$key}",
                Crypt::encryptString($value),
                now()->addDays(90)
            );
        }
    }
}
```

## Request Signing
```php
trait SignsMetaRequests
{
    protected function signRequest(array $params): string
    {
        ksort($params);
        return hash_hmac(
            'sha256',
            http_build_query($params),
            config('meta.app_secret')
        );
    }
}
```

## Audit Logging
```php
trait LogsMetaAccess
{
    protected function logAccess(string $action, array $context = []): void
    {
        MetaAuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'ip' => request()->ip(),
            'context' => $context,
            'timestamp' => now()
        ]);
    }
}
```