<?php

namespace EnricoDeLazzari\ClaudeMonitor\Support;

final readonly class BudgetMetrics
{
    public function __construct(
        public float $spent,
        public float $totalBudget,
        public float $baseBudget,
        public float $contingencyAmount,
        public float $remaining,
        public float $percentage,
        public float $basePercentage,
        public float $dailyAverage,
        public float $projected,
        public float $projectedPct,
        public float $projectedVsBase,
        public float $calendarPct,
        public float $basePct,
        public int $dayOfMonth,
        public int $daysInMonth,
        public string $currencySymbol,
    ) {}
}
