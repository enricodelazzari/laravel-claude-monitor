<?php

namespace EnricoDeLazzari\ClaudeMonitor;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ClaudeMonitorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-claude-monitor')
            ->hasMigrations([
                'create_settings_table',
                'create_day_offs_table',
            ]);
    }
}
