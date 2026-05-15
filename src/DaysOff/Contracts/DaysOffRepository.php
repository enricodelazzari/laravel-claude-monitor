<?php

namespace EnricoDeLazzari\ClaudeMonitor\DaysOff\Contracts;

use Illuminate\Support\Carbon;

interface DaysOffRepository
{
    /** @return list<string> */
    public function datesForMonth(Carbon $date): array;

    public function countForMonth(Carbon $date): int;

    public function add(string $date, ?string $note = null): void;

    public function remove(string $date): void;

    public function has(string $date): bool;
}
