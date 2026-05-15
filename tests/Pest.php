<?php

use EnricoDeLazzari\ClaudeMonitor\ClaudeAi\Requests\GetAccountRequest;
use EnricoDeLazzari\ClaudeMonitor\ClaudeAi\Requests\GetOrganizationsRequest;
use EnricoDeLazzari\ClaudeMonitor\ClaudeAi\Requests\GetOverageSpendLimitRequest;
use EnricoDeLazzari\ClaudeMonitor\ClaudeAi\Requests\GetUsageRequest;
use EnricoDeLazzari\ClaudeMonitor\Tests\TestCase;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

uses(TestCase::class)->in(__DIR__);

function fakeClaudeApi(array $usage = [], array $overage = [], array $account = []): void
{
    Saloon::fake([
        GetAccountRequest::class => MockResponse::make(array_merge([
            'uuid' => 'user-123',
            'email_address' => 'test@example.com',
        ], $account)),
        GetOrganizationsRequest::class => MockResponse::make([
            ['uuid' => 'org-123', 'name' => 'Test Org', 'billing_type' => null],
        ]),
        GetUsageRequest::class => MockResponse::make([
            'extra_usage' => array_merge([
                'is_enabled' => true,
                'monthly_limit' => 30000,
                'used_credits' => 15000,
                'utilization' => 50.0,
                'currency' => 'USD',
            ], $usage['extra_usage'] ?? []),
            'five_hour' => $usage['five_hour'] ?? ['utilization' => 0.5, 'resets_at' => null],
            'seven_day' => $usage['seven_day'] ?? ['utilization' => 0.23, 'resets_at' => null],
            'seven_day_opus' => $usage['seven_day_opus'] ?? ['utilization' => 0.55, 'resets_at' => null],
            'seven_day_sonnet' => $usage['seven_day_sonnet'] ?? ['utilization' => 0.15, 'resets_at' => null],
        ]),
        GetOverageSpendLimitRequest::class => MockResponse::make(array_merge([
            'is_enabled' => false,
            'used_credits' => 0,
            'monthly_credit_limit' => 0,
            'currency' => 'USD',
        ], $overage)),
    ]);
}
