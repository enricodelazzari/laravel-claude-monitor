<?php

use EnricoDeLazzari\ClaudeMonitor\ClaudeAi\Requests\GetAccountRequest;
use EnricoDeLazzari\ClaudeMonitor\ClaudeAi\Requests\GetOverageSpendLimitRequest;
use EnricoDeLazzari\ClaudeMonitor\ClaudeAi\Requests\GetUsageRequest;
use EnricoDeLazzari\ClaudeMonitor\Settings\Contracts\SettingsRepository;
use EnricoDeLazzari\ClaudeMonitor\Support\ClaudeContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Saloon\Http\Faking\MockClient;

uses(RefreshDatabase::class);

beforeEach(function () {
    app(SettingsRepository::class)->set('session_key', 'test-session-key');
});

afterEach(function () {
    MockClient::destroyGlobal();
});

it('caches usage between calls and re-fetches after refresh', function () {
    fakeClaudeApi();
    $ctx = app(ClaudeContext::class);

    $ctx->usage();
    $ctx->usage();

    MockClient::getGlobal()->assertSentCount(1, GetUsageRequest::class);

    $ctx->refresh();
    $ctx->usage();

    MockClient::getGlobal()->assertSentCount(2, GetUsageRequest::class);
});

it('refresh invalidates account and overage too', function () {
    fakeClaudeApi();
    $ctx = app(ClaudeContext::class);

    $ctx->account();
    $ctx->overage();
    $ctx->account();
    $ctx->overage();

    MockClient::getGlobal()->assertSentCount(1, GetAccountRequest::class);
    MockClient::getGlobal()->assertSentCount(1, GetOverageSpendLimitRequest::class);

    $ctx->refresh();
    $ctx->account();
    $ctx->overage();

    MockClient::getGlobal()->assertSentCount(2, GetAccountRequest::class);
    MockClient::getGlobal()->assertSentCount(2, GetOverageSpendLimitRequest::class);
});
