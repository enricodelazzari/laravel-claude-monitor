<?php

namespace EnricoDeLazzari\ClaudeMonitor\Support;

use EnricoDeLazzari\ClaudeMonitor\Models\DayOff;
use EnricoDeLazzari\ClaudeMonitor\Settings\Contracts\SettingsRepository;
use Illuminate\Support\Carbon;
use Spatie\Holidays\Holidays;

class PaceCalculator
{
    public function __construct(
        private SettingsRepository $settings,
    ) {}

    public function from(BudgetMetrics $budget, Carbon $now): PaceMetrics
    {
        $country = (string) $this->settings->get('holidays_country', 'it');
        $holidays = $this->holidayDates($now, $country);

        $startOfMonth = $now->copy()->startOfMonth();
        $totalWorkingDays = $this->countWorkingDays($startOfMonth, $now->copy()->endOfMonth(), $holidays);
        $elapsedWorkingDays = $this->countWorkingDays($startOfMonth, $now->copy()->startOfDay(), $holidays);
        $remainingWorkingDays = $totalWorkingDays - $elapsedWorkingDays;

        $dailyTarget = $totalWorkingDays > 0 ? $budget->baseBudget / $totalWorkingDays : 0.0;
        $expectedByNow = $dailyTarget * $elapsedWorkingDays;
        $remainingBase = max(0.0, $budget->baseBudget - $budget->spent);

        return new PaceMetrics(
            holidayLocale: $country,
            customDaysOffCount: DayOff::forMonth($now)->count(),
            totalWorkingDays: $totalWorkingDays,
            elapsedWorkingDays: $elapsedWorkingDays,
            remainingWorkingDays: $remainingWorkingDays,
            dailyTarget: $dailyTarget,
            expectedByNow: $expectedByNow,
            budgetPerRemainingDay: $remainingWorkingDays > 0 ? $remainingBase / $remainingWorkingDays : 0.0,
            variance: $expectedByNow - $budget->spent,
            ratio: $expectedByNow > 0 ? $budget->spent / $expectedByNow : 0.0,
            timePct: $totalWorkingDays > 0 ? ($elapsedWorkingDays / $totalWorkingDays) * 100 : 0.0,
            spendingPct: $budget->baseBudget > 0 ? ($budget->spent / $budget->baseBudget) * 100 : 0.0,
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
            ->all();

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
