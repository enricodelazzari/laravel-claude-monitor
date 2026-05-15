<?php

namespace EnricoDeLazzari\ClaudeMonitor\Support;

final readonly class PaceMetrics
{
    /**
     * @param  list<string>  $holidays
     */
    public function __construct(
        public string $holidayLocale,
        public int $customDaysOffCount,
        public int $totalWorkingDays,
        public int $elapsedWorkingDays,
        public int $remainingWorkingDays,
        public float $dailyTarget,
        public float $expectedByNow,
        public float $budgetPerRemainingDay,
        public float $variance,
        public float $ratio,
        public float $timePct,
        public float $spendingPct,
        public array $holidays,
    ) {}
}
