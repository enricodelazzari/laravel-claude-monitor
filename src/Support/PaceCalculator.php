<?php

namespace EnricoDeLazzari\ClaudeMonitor\Support;

use EnricoDeLazzari\ClaudeMonitor\Models\DayOff;
use EnricoDeLazzari\ClaudeMonitor\Models\Setting;
use Illuminate\Support\Carbon;
use Spatie\Holidays\Holidays;

class PaceCalculator
{
    public function from(BudgetMetrics $budget, Carbon $now): PaceMetrics
    {
        $country = Setting::get('holidays_country', 'it');

        $holidays = $this->holidayDates($now, $country);
        $totalWorkingDays = $this->workingDaysInMonth($now, $holidays);
        $elapsedWorkingDays = $this->workingDaysElapsed($now, $holidays);
        $remainingWorkingDays = $totalWorkingDays - $elapsedWorkingDays;
        $dailyTarget = $totalWorkingDays > 0 ? $budget->baseBudget / $totalWorkingDays : 0.0;
        $expectedByNow = $dailyTarget * $elapsedWorkingDays;

        $remainingBase = max(0.0, $budget->baseBudget - $budget->spent);
        $budgetPerRemainingDay = $remainingWorkingDays > 0 ? $remainingBase / $remainingWorkingDays : 0.0;

        $variance = $expectedByNow - $budget->spent;
        $ratio = $expectedByNow > 0 ? $budget->spent / $expectedByNow : 0.0;

        $timePct = $totalWorkingDays > 0 ? ($elapsedWorkingDays / $totalWorkingDays) * 100 : 0.0;
        $spendingPct = $budget->baseBudget > 0 ? ($budget->spent / $budget->baseBudget) * 100 : 0.0;

        $customDaysOffCount = DayOff::forMonth($now)->count();

        return new PaceMetrics(
            holidayLocale: $country,
            customDaysOffCount: $customDaysOffCount,
            totalWorkingDays: $totalWorkingDays,
            elapsedWorkingDays: $elapsedWorkingDays,
            remainingWorkingDays: $remainingWorkingDays,
            dailyTarget: $dailyTarget,
            expectedByNow: $expectedByNow,
            budgetPerRemainingDay: $budgetPerRemainingDay,
            variance: $variance,
            ratio: $ratio,
            timePct: $timePct,
            spendingPct: $spendingPct,
            holidays: $holidays,
        );
    }

    /** @return list<string> */
    private function holidayDates(Carbon $now, string $country): array
    {
        $publicHolidays = array_map(
            fn ($holiday) => $holiday->date->format('Y-m-d'),
            Holidays::for(country: $country, year: $now->year)->get()
        );

        $customDaysOff = DayOff::forMonth($now)
            ->pluck('date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->toArray();

        return array_values(array_unique(array_merge($publicHolidays, $customDaysOff)));
    }

    /** @param  list<string>  $holidays */
    private function workingDaysInMonth(Carbon $now, array $holidays): int
    {
        $day = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();
        $count = 0;
        while ($day <= $end) {
            if ($day->isWeekday() && ! in_array($day->format('Y-m-d'), $holidays)) {
                $count++;
            }
            $day->addDay();
        }

        return $count;
    }

    /** @param  list<string>  $holidays */
    private function workingDaysElapsed(Carbon $now, array $holidays): int
    {
        $day = $now->copy()->startOfMonth();
        $today = $now->copy()->startOfDay();
        $count = 0;
        while ($day <= $today) {
            if ($day->isWeekday() && ! in_array($day->format('Y-m-d'), $holidays)) {
                $count++;
            }
            $day->addDay();
        }

        return $count;
    }
}
