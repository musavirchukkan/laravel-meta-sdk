<?php

namespace YourVendor\MetaSdk\Tests\Mocks;

class MetaMockResponses
{
    public static function leadForms(): array
    {
        return [
            'data' => [
                [
                    'id' => '123456789',
                    'name' => 'Test Form',
                    'status' => 'ACTIVE',
                    'leads_count' => 10
                ]
            ]
        ];
    }

    public static function formLeads(): array
    {
        return [
            'data' => [
                [
                    'id' => '123456789',
                    'created_time' => '2024-01-01T10:00:00+0000',
                    'field_data' => [
                        [
                            'name' => 'email',
                            'values' => ['test@example.com']
                        ]
                    ]
                ]
            ]
        ];
    }

    public static function campaigns(): array
    {
        return [
            'data' => [
                [
                    'id' => '123456789',
                    'name' => 'Test Campaign',
                    'objective' => 'LEAD_GENERATION',
                    'status' => 'ACTIVE',
                    'daily_budget' => '1000'
                ]
            ]
        ];
    }

    public static function insights(): array
    {
        return [
            'data' => [
                [
                    'impressions' => '1000',
                    'clicks' => '100',
                    'spend' => '50.00',
                    'ctr' => '10'
                ]
            ]
        ];
    }
}