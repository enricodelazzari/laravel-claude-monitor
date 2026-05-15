<?php

use EnricoDeLazzari\ClaudeMonitor\DaysOff\Repositories\DatabaseDaysOffRepository;
use EnricoDeLazzari\ClaudeMonitor\Models\DayOff;
use EnricoDeLazzari\ClaudeMonitor\Models\Setting;
use EnricoDeLazzari\ClaudeMonitor\Settings\Repositories\DatabaseSettingsRepository;

return [
    'default_repository' => env('CLAUDE_MONITOR_SETTINGS_REPO', 'database'),

    'repositories' => [
        'database' => [
            'driver' => DatabaseSettingsRepository::class,
            'model' => Setting::class,
            'connection' => null,
        ],
    ],

    'default_days_off_repository' => env('CLAUDE_MONITOR_DAYS_OFF_REPO', 'database'),

    'days_off_repositories' => [
        'database' => [
            'driver' => DatabaseDaysOffRepository::class,
            'model' => DayOff::class,
            'connection' => null,
        ],
    ],
];
