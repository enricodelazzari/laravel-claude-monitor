<?php

namespace EnricoDeLazzari\ClaudeMonitor\DaysOff;

use EnricoDeLazzari\ClaudeMonitor\DaysOff\Contracts\DaysOffRepository;
use EnricoDeLazzari\ClaudeMonitor\DaysOff\Exceptions\InvalidDaysOffRepository;
use Illuminate\Support\Arr;

final class DaysOffRepositoryFactory
{
    public static function create(?string $name = null): DaysOffRepository
    {
        $name ??= (string) config('claude-monitor.default_days_off_repository');
        $config = config("claude-monitor.days_off_repositories.{$name}");

        if (! is_array($config) || ! isset($config['driver'])) {
            throw InvalidDaysOffRepository::unknown($name);
        }

        return app($config['driver'], [
            'config' => Arr::except($config, 'driver'),
        ]);
    }
}
