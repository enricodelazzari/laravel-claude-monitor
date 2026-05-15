<?php

namespace EnricoDeLazzari\ClaudeMonitor\Support;

use EnricoDeLazzari\ClaudeMonitor\DaysOff\Contracts\DaysOffRepository;
use EnricoDeLazzari\ClaudeMonitor\Settings\Contracts\SettingsRepository;
use Illuminate\Support\Carbon;
use Spatie\Holidays\Holidays;

class PaceCalculator
{
    public function __construct(
        private SettingsRepository $settings,
        private DaysOffRepository $daysOff,
    ) {}

    public function from(BudgetMetrics $budget, Carbon $now): PaceMetrics
    {
        $country = (string) $this->settings->get('holidays_country', 'it');
        $holidays = $this->holidayDates($now, $country);

        $startOfMonth = $now->copy()->startOfMonth();
        $totalWorkingDays = $this->countWorkingDays($startOfMonth, $now->copy()->endOfMonth(), $holidays);
        $elapsedWorkingDays = $this->countWorkingDays($startOfMonth, $now->copy()->startOfDay(), $holidays);
        $remainingWorkingDays = $totalWorkingDays - $elapsedWorkingDays;

        $dailyTarget = $this->safeDivide($budget->baseBudget, $totalWorkingDays);
        $expectedByNow = $dailyTarget * $elapsedWorkingDays;
        $remainingBase = max(0.0, $budget->baseBudget - $budget->spent);

        return new PaceMetrics(
            holidayLocale: $country,
            customDaysOffCount: $this->daysOff->countForMonth($now),
            totalWorkingDays: $totalWorkingDays,
            elapsedWorkingDays: $elapsedWorkingDays,
            remainingWorkingDays: $remainingWorkingDays,
            dailyTarget: $dailyTarget,
            expectedByNow: $expectedByNow,
            budgetPerRemainingDay: $this->safeDivide($remainingBase, $remainingWorkingDays),
            variance: $expectedByNow - $budget->spent,
            ratio: $this->safeDivide($budget->spent, $expectedByNow),
            timePct: $this->safeDivide($elapsedWorkingDays, $totalWorkingDays) * 100,
            spendingPct: $this->safeDivide($budget->spent, $budget->baseBudget) * 100,
            holidays: $holidays,
        );
    }

    private function safeDivide(float|int $numerator, float|int $denominator): float
    {
        return $denominator > 0 ? $numerator / $denominator : 0.0;
    }

    /** @return list<string> */
    private function holidayDates(Carbon $now, string $country): array
    {
        $publicHolidays = array_map(
            fn ($holiday) => $holiday->date->format('Y-m-d'),
            Holidays::for(country: $country, year: $now->year)->get()
        );

        $customDaysOff = $this->daysOff->datesForMonth($now);

        return array_values(array_unique([...$publicHolidays, ...$customDaysOff]));
    }

    /** @param  list<string>  $holidays */
    private function countWorkingDays(Carbon $start, Carbon $end, array $holidays): int
    {
        $day = $start->copy();
        $count = 0;

        while ($day <= $end) {
            if ($day->isWeekday() && ! in_array($day->format('Y-m-d'), $holidays, true)) {
                $count++;
            }
            $day->addDay();
        }

        return $count;
    }
}
