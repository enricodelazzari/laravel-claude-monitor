<?php

namespace EnricoDeLazzari\ClaudeMonitor\Settings\Repositories;

use EnricoDeLazzari\ClaudeMonitor\Models\Setting;
use EnricoDeLazzari\ClaudeMonitor\Settings\Contracts\SettingsRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSettingsRepository implements SettingsRepository
{
    /** @var class-string<Model> */
    private string $model;

    private ?string $connection;

    /** @param array{model?: class-string<Model>|null, connection?: string|null} $config */
    public function __construct(array $config = [])
    {
        $this->model = $config['model'] ?? Setting::class;
        $this->connection = $config['connection'] ?? null;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query()->where('key', $key)->value('value') ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public function has(string $key): bool
    {
        return $this->query()->where('key', $key)->exists();
    }

    public function forget(string $key): void
    {
        $this->query()->where('key', $key)->delete();
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->query()->pluck('value', 'key')->all();
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
