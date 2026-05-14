<?php

namespace EnricoDeLazzari\ClaudeMonitor;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use EnricoDeLazzari\ClaudeMonitor\Commands\ClaudeMonitorCommand;

class ClaudeMonitorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-claude-monitor')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_claude_monitor_table')
            ->hasCommand(ClaudeMonitorCommand::class);
    }
}
