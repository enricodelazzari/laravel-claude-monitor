<?php

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
];
