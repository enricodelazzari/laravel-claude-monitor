<?php

namespace EnricoDeLazzari\ClaudeMonitor\Support;

use EnricoDeLazzari\ClaudeMonitor\Models\Setting;
use Illuminate\Support\Carbon;

class MetricsCalculator
{
    public function from(
        array $usage,
        ?float $budgetOverride,
        ?float $contingencyOverride,
        Carbon $now,
    ): BudgetMetrics {
        $extraUsage = $usage['extra_usage'] ?? [];
        $currency = $extraUsage['currency'] ?? 'USD';
        $symbol = $currency === 'USD' ? '$' : $currency.' ';

        $spent = (int) ($extraUsage['used_credits'] ?? 0) / 100;
        $totalBudget = $this->resolveTotalBudget((int) ($extraUsage['monthly_limit'] ?? 0), $budgetOverride);
        $contingencyAmount = $totalBudget * ($this->resolveContingencyPct($contingencyOverride) / 100);
        $baseBudget = $totalBudget - $contingencyAmount;

        $dayOfMonth = $now->day;
        $daysInMonth = $now->daysInMonth;
        $dailyAverage = $dayOfMonth > 0 ? $spent / $dayOfMonth : 0;
        $projected = $dailyAverage * $daysInMonth;
        $percentage = $totalBudget > 0 ? ($spent / $totalBudget) * 100 : 0;
        $basePercentage = $baseBudget > 0 ? ($spent / $baseBudget) * 100 : 0;
        $remaining = max(0.0, $totalBudget - $spent);
        $calendarPct = $daysInMonth > 0 ? ($dayOfMonth / $daysInMonth) * 100 : 0;
        $basePct = $totalBudget > 0 ? ($baseBudget / $totalBudget) * 100 : 0;
        $projectedPct = $totalBudget > 0 ? ($projected / $totalBudget) * 100 : 0;
        $projectedVsBase = $baseBudget > 0 ? ($projected / $baseBudget) * 100 : 0;

        return new BudgetMetrics(
            spent: $spent,
            totalBudget: $totalBudget,
            baseBudget: $baseBudget,
            contingencyAmount: $contingencyAmount,
            remaining: $remaining,
            percentage: $percentage,
            basePercentage: $basePercentage,
            dailyAverage: $dailyAverage,
            projected: $projected,
            projectedPct: $projectedPct,
            projectedVsBase: $projectedVsBase,
            calendarPct: $calendarPct,
            basePct: $basePct,
            dayOfMonth: $dayOfMonth,
            daysInMonth: $daysInMonth,
            currencySymbol: $symbol,
        );
    }

    private function resolveTotalBudget(int $apiLimitCents, ?float $override): float
    {
        $budget = $override ?? (float) Setting::get('monthly_budget', '0');

        if ($budget > 0) {
            return $budget;
        }

        return $apiLimitCents > 0 ? $apiLimitCents / 100 : 0.0;
    }

    private function resolveContingencyPct(?float $override): float
    {
        $pct = $override ?? (float) Setting::get('contingency_pct', '0');

        return max(0.0, min(100.0, $pct));
    }
}
