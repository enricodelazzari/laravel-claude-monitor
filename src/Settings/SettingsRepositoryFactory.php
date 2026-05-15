<?php

namespace EnricoDeLazzari\ClaudeMonitor\Settings;

use EnricoDeLazzari\ClaudeMonitor\Settings\Contracts\SettingsRepository;
use EnricoDeLazzari\ClaudeMonitor\Settings\Exceptions\InvalidSettingsRepository;
use Illuminate\Support\Arr;

final class SettingsRepositoryFactory
{
    public static function create(?string $name = null): SettingsRepository
    {
        $name ??= (string) config('claude-monitor.default_repository');
        $config = config("claude-monitor.repositories.{$name}");

        if (! is_array($config) || ! isset($config['driver'])) {
            throw InvalidSettingsRepository::unknown($name);
        }

        return app($config['driver'], [
            'config' => Arr::except($config, 'driver'),
        ]);
    }
}
