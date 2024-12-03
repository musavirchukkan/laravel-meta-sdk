# Laravel Meta SDK

A Laravel SDK for Meta Platform integration focusing on leads management, campaign metrics, ad performance analytics, and form integration.

## Requirements
- PHP 8.1+
- Laravel 10+
- Meta Business Account
- Meta App with required permissions

## Installation
```bash
composer require yourusername/laravel-meta-sdk
```

Publish config:
```bash
php artisan vendor:publish --tag="meta-config"
```

## Configuration
Add to .env:
```env
META_CLIENT_ID=your_app_id
META_CLIENT_SECRET=your_app_secret
META_REDIRECT_URI=your_callback_url
META_API_VERSION=v18.0
```

## Usage

### Authentication
```php
use YourVendor\MetaSdk\Facades\Meta;

// Get login URL
$url = Meta::auth()->getAuthorizationUrl();

// Handle callback
$result = Meta::auth()->handleCallback($code);
```

### Leads Management
```php
// Get forms
$forms = Meta::leads()->getLeadForms($pageId, $token);

// Get form leads
$leads = Meta::leads()->getFormLeads($formId, $token);
```

### Campaign Management
```php
// Get campaigns
$campaigns = Meta::campaigns()->getCampaigns($adAccountId, $token);

// Get insights
$insights = Meta::campaigns()->getCampaignInsights($campaignId, $token);
```

### Metrics
```php
// Get metrics summary
$metrics = Meta::metrics()->getMetricsSummary('leads');

// Via Command
php artisan meta:metrics
php artisan meta:metrics:clear
```

## Features
- Auth flow management
- Lead forms integration
- Campaign metrics
- Ad performance analytics
- Rate limiting
- Error handling
- Caching
- Logging
- Metrics collection

## Testing
```bash
composer test
```

## Code Quality
```bash
composer check
```

## Security
See [SECURITY.md](SECURITY.md)

## Contributing
See [CONTRIBUTING.md](.github/CONTRIBUTING.md)

## License
MIT