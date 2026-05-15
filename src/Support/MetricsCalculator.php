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

        $spent = (int) ($extraUsage['used_credits'] ?? 0) / 100;
        $totalBudget = $this->resolveTotalBudget((int) ($extraUsage['monthly_limit'] ?? 0), $budgetOverride);
        $contingencyAmount = $totalBudget * ($this->resolveContingencyPct($contingencyOverride) / 100);
        $baseBudget = $totalBudget - $contingencyAmount;

        $dayOfMonth = $now->day;
        $daysInMonth = $now->daysInMonth;
        $dailyAverage = $dayOfMonth > 0 ? $spent / $dayOfMonth : 0.0;
        $projected = $dailyAverage * $daysInMonth;

        return new BudgetMetrics(
            spent: $spent,
            totalBudget: $totalBudget,
            baseBudget: $baseBudget,
            contingencyAmount: $contingencyAmount,
            remaining: max(0.0, $totalBudget - $spent),
            percentage: $this->ratioPct($spent, $totalBudget),
            basePercentage: $this->ratioPct($spent, $baseBudget),
            dailyAverage: $dailyAverage,
            projected: $projected,
            projectedPct: $this->ratioPct($projected, $totalBudget),
            projectedVsBase: $this->ratioPct($projected, $baseBudget),
            calendarPct: $this->ratioPct($dayOfMonth, $daysInMonth),
            basePct: $this->ratioPct($baseBudget, $totalBudget),
            dayOfMonth: $dayOfMonth,
            daysInMonth: $daysInMonth,
            currencySymbol: $currency === 'USD' ? '$' : $currency.' ',
        );
    }

    private function ratioPct(float|int $numerator, float|int $denominator): float
    {
        return $denominator > 0 ? ($numerator / $denominator) * 100 : 0.0;
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
