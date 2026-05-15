<?php

namespace EnricoDeLazzari\ClaudeMonitor\Database\Factories;

use EnricoDeLazzari\ClaudeMonitor\Models\DayOff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DayOff>
 */
class DayOffFactory extends Factory
{
    protected $model = DayOff::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'date' => fake()->dateTimeThisMonth()->format('Y-m-d'),
            'note' => null,
        ];
    }

    public function forDate(string $date): static
    {
        return $this->state(['date' => $date]);
    }

    public function withNote(string $note): static
    {
        return $this->state(['note' => $note]);
    }
}
