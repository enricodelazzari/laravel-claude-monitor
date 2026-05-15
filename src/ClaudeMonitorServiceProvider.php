<?php

namespace EnricoDeLazzari\ClaudeMonitor;

use EnricoDeLazzari\ClaudeMonitor\Settings\Contracts\SettingsRepository;
use EnricoDeLazzari\ClaudeMonitor\Settings\SettingsRepositoryFactory;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ClaudeMonitorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-claude-monitor')
            ->hasConfigFile()
            ->hasMigrations([
                'create_settings_table',
                'create_day_offs_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->bind(
            SettingsRepository::class,
            fn () => SettingsRepositoryFactory::create(),
        );
    }
}
