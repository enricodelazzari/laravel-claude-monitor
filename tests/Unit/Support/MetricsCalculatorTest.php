<?php

use EnricoDeLazzari\ClaudeMonitor\Support\BudgetMetrics;
use EnricoDeLazzari\ClaudeMonitor\Support\MetricsCalculator;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->calc = new MetricsCalculator;
});

it('computes spent in dollars from used_credits cents', function () {
    $metrics = $this->calc->from(
        ['extra_usage' => ['used_credits' => 9024, 'monthly_limit' => 30000, 'currency' => 'USD']],
        null,
        null,
        Carbon::parse('2026-05-15'),
    );

    expect($metrics)->toBeInstanceOf(BudgetMetrics::class)
        ->and($metrics->spent)->toBe(90.24)
        ->and($metrics->totalBudget)->toBe(300.0)
        ->and($metrics->currencySymbol)->toBe('$');
});

it('uses budget override when provided', function () {
    $metrics = $this->calc->from(
        ['extra_usage' => ['used_credits' => 5000, 'monthly_limit' => 30000]],
        500.0,
        null,
        Carbon::parse('2026-05-15'),
    );

    expect($metrics->totalBudget)->toBe(500.0);
});

it('falls back to api monthly_limit when no setting is configured', function () {
    $metrics = $this->calc->from(
        ['extra_usage' => ['used_credits' => 0, 'monthly_limit' => 25000]],
        null,
        null,
        Carbon::parse('2026-05-15'),
    );

    expect($metrics->totalBudget)->toBe(250.0);
});

it('returns 0 when no setting and api budget are both zero', function () {
    $metrics = $this->calc->from(
        ['extra_usage' => ['used_credits' => 0, 'monthly_limit' => 0]],
        null,
        null,
        Carbon::parse('2026-05-15'),
    );

    expect($metrics->totalBudget)->toBe(0.0);
});

it('splits total into base and contingency based on pct', function () {
    $metrics = $this->calc->from(
        ['extra_usage' => ['used_credits' => 0]],
        300.0,
        10.0,
        Carbon::parse('2026-05-15'),
    );

    expect($metrics->totalBudget)->toBe(300.0)
        ->and($metrics->baseBudget)->toBe(270.0)
        ->and($metrics->contingencyAmount)->toBe(30.0);
});

it('clamps contingency percentage to 0-100 range', function () {
    $over = $this->calc->from(['extra_usage' => []], 300.0, 150.0, Carbon::parse('2026-05-15'));
    expect($over->contingencyAmount)->toBe(300.0);

    $under = $this->calc->from(['extra_usage' => []], 300.0, -10.0, Carbon::parse('2026-05-15'));
    expect($under->contingencyAmount)->toBe(0.0);
});

it('projects month spend from daily average', function () {
    // day 15 of May (31 days), spent $90 → daily $6.00 → projection $186.00
    $metrics = $this->calc->from(
        ['extra_usage' => ['used_credits' => 9000]],
        300.0,
        null,
        Carbon::parse('2026-05-15'),
    );

    expect($metrics->dailyAverage)->toBe(6.0)
        ->and($metrics->projected)->toBe(186.0)
        ->and($metrics->dayOfMonth)->toBe(15)
        ->and($metrics->daysInMonth)->toBe(31);
});

it('uses non-USD currency string as symbol prefix with space', function () {
    $metrics = $this->calc->from(
        ['extra_usage' => ['used_credits' => 5000, 'monthly_limit' => 30000, 'currency' => 'EUR']],
        null,
        null,
        Carbon::parse('2026-05-15'),
    );

    expect($metrics->currencySymbol)->toBe('EUR ');
});

it('caps remaining at zero when overspent', function () {
    $metrics = $this->calc->from(
        ['extra_usage' => ['used_credits' => 35000]],
        300.0,
        null,
        Carbon::parse('2026-05-15'),
    );

    expect($metrics->remaining)->toBe(0.0)
        ->and($metrics->spent)->toBe(350.0);
});
