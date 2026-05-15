<?php

namespace EnricoDeLazzari\ClaudeMonitor\DaysOff\Repositories;

use EnricoDeLazzari\ClaudeMonitor\DaysOff\Contracts\DaysOffRepository;
use EnricoDeLazzari\ClaudeMonitor\Models\DayOff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DatabaseDaysOffRepository implements DaysOffRepository
{
    /** @var class-string<Model> */
    private string $model;

    private ?string $connection;

    /** @param array{model?: class-string<Model>|null, connection?: string|null} $config */
    public function __construct(array $config = [])
    {
        $this->model = $config['model'] ?? DayOff::class;
        $this->connection = $config['connection'] ?? null;
    }

    /** @return list<string> */
    public function datesForMonth(Carbon $date): array
    {
        return $this->query()
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->pluck('date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->all();
    }

    public function countForMonth(Carbon $date): int
    {
        return $this->query()
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->count();
    }

    public function add(string $date, ?string $note = null): void
    {
        $this->query()->updateOrCreate(['date' => $date], ['note' => $note]);
    }

    public function remove(string $date): void
    {
        $this->query()->where('date', $date)->delete();
    }

    public function has(string $date): bool
    {
        return $this->query()->where('date', $date)->exists();
    }

    private function query(): Builder
    {
        $model = new $this->model;

        if ($this->connection !== null) {
            $model->setConnection($this->connection);
        }

        return $model->newQuery();
    }
}
