<?php

use EnricoDeLazzari\ClaudeMonitor\Models\DayOff;
use EnricoDeLazzari\ClaudeMonitor\Support\BudgetMetrics;
use EnricoDeLazzari\ClaudeMonitor\Support\PaceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->calc = app(PaceCalculator::class);
});

function makeBudget(float $spent, float $baseBudget = 300.0): BudgetMetrics
{
    return new BudgetMetrics(
        spent: $spent,
        totalBudget: $baseBudget,
        baseBudget: $baseBudget,
        contingencyAmount: 0.0,
        remaining: max(0.0, $baseBudget - $spent),
        percentage: 0.0,
        basePercentage: 0.0,
        dailyAverage: 0.0,
        projected: 0.0,
        projectedPct: 0.0,
        projectedVsBase: 0.0,
        calendarPct: 0.0,
        basePct: 100.0,
        dayOfMonth: 1,
        daysInMonth: 31,
        currencySymbol: '$',
    );
}

it('counts working days in May 2026 with Italian holidays', function () {
    // May 1 2026 = Labor Day (it holiday), May 23-24 + 30-31 weekends excluded
    $pace = $this->calc->from(makeBudget(0), Carbon::parse('2026-05-15'));

    expect($pace->totalWorkingDays)->toBe(20)
        ->and($pace->holidayLocale)->toBe('it');
});

it('counts elapsed working days from start of month', function () {
    // May 1 is Friday but holiday → 0; May 4,5,6,7,8 = 5 elapsed by May 8
    $pace = $this->calc->from(makeBudget(0), Carbon::parse('2026-05-08'));

    expect($pace->elapsedWorkingDays)->toBe(5)
        ->and($pace->remainingWorkingDays)->toBe(15);
});

it('computes daily target as base budget over working days', function () {
    $pace = $this->calc->from(makeBudget(0, 300.0), Carbon::parse('2026-05-15'));

    expect($pace->dailyTarget)->toBe(15.0);
});

it('reduces working days when custom day off is added', function () {
    DayOff::factory()->forDate('2026-05-06')->create();
    DayOff::factory()->forDate('2026-05-07')->create();

    $pace = $this->calc->from(makeBudget(0), Carbon::parse('2026-05-08'));

    expect($pace->totalWorkingDays)->toBe(18)
        ->and($pace->customDaysOffCount)->toBe(2)
        ->and($pace->elapsedWorkingDays)->toBe(3); // May 4,5,8
});

it('computes variance positive when ahead of pace', function () {
    // 5 elapsed days × $15 target = $75 expected; spent $60 → variance +$15, ratio 0.80
    $pace = $this->calc->from(makeBudget(60.0), Carbon::parse('2026-05-08'));

    expect($pace->expectedByNow)->toBe(75.0)
        ->and($pace->variance)->toBe(15.0)
        ->and($pace->ratio)->toBe(0.8);
});

it('computes budget per remaining working day from base budget', function () {
    // 5 elapsed, 15 remaining; base=$300, spent=$60 → remaining base $240 → $16/day
    $pace = $this->calc->from(makeBudget(60.0), Carbon::parse('2026-05-08'));

    expect($pace->budgetPerRemainingDay)->toBe(16.0);
});

it('returns zero ratio when no working days have elapsed', function () {
    // 2026-05-01 is a Friday but a holiday in Italy → 0 elapsed
    $pace = $this->calc->from(makeBudget(0), Carbon::parse('2026-05-01'));

    expect($pace->elapsedWorkingDays)->toBe(0)
        ->and($pace->ratio)->toBe(0.0);
});
