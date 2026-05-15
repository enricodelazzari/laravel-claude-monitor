# laravel-claude-monitor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/enricodelazzari/laravel-claude-monitor.svg?style=flat-square)](https://packagist.org/packages/enricodelazzari/laravel-claude-monitor)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/enricodelazzari/laravel-claude-monitor/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/enricodelazzari/laravel-claude-monitor/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/enricodelazzari/laravel-claude-monitor/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/enricodelazzari/laravel-claude-monitor/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/enricodelazzari/laravel-claude-monitor.svg?style=flat-square)](https://packagist.org/packages/enricodelazzari/laravel-claude-monitor)

A Laravel package to monitor your Claude AI API spending. It connects to the Claude.ai web interface using your session key to fetch real-time usage, calculates budget metrics (spent, remaining, projected), and tracks your spending pace relative to working days — accounting for national holidays and custom days off.

## Installation

Install the package via Composer:

```bash
composer require enricodelazzari/laravel-claude-monitor
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="laravel-claude-monitor-migrations"
php artisan migrate
```

Publish the config file:

```bash
php artisan vendor:publish --tag="laravel-claude-monitor-config"
```

This is the contents of the published config file:

```php
return [
    'default_repository' => env('CLAUDE_MONITOR_SETTINGS_REPO', 'database'),

    'repositories' => [
        'database' => [
            'driver' => \EnricoDeLazzari\ClaudeMonitor\Settings\Repositories\DatabaseSettingsRepository::class,
            'model'  => \EnricoDeLazzari\ClaudeMonitor\Models\Setting::class,
            'connection' => null,
        ],
    ],
];
```

## Usage

### Storing settings

The package persists configuration in the database via the `SettingsRepository`. Start by storing your Claude.ai session key:

```php
use EnricoDeLazzari\ClaudeMonitor\Settings\Contracts\SettingsRepository;

app(SettingsRepository::class)->set('session_key', 'your-claude-session-key');
```

Available settings keys:

| Key | Type | Description |
|-----|------|-------------|
| `session_key` | `string` | Claude.ai session cookie (required) |
| `monthly_budget` | `float` | Monthly budget override in USD |
| `contingency_pct` | `float` | Contingency % reserved from the base budget (0–100) |
| `holidays_country` | `string` | ISO country code for the holiday calendar (default: `it`) |

### Fetching usage data

`ClaudeContext` is a lazy-loading container for the Claude.ai session. All results are cached in-memory for the lifetime of the object; call `refresh()` to clear them.

```php
use EnricoDeLazzari\ClaudeMonitor\Support\ClaudeContext;

$context = app(ClaudeContext::class);

$context->account();       // ?array — Claude account info
$context->organization();  // array  — best available organization (paid plan preferred)
$context->orgId();         // string — organization UUID
$context->usage();         // array  — raw usage/spending data
$context->overage();       // ?array — overage spend limit
$context->refresh();       // clears all cached data
```

### Budget metrics

`MetricsCalculator` turns raw usage data into a readonly `BudgetMetrics` object. Pass `null` for the override parameters to fall back to the stored settings.

```php
use EnricoDeLazzari\ClaudeMonitor\Support\MetricsCalculator;
use Illuminate\Support\Carbon;

$metrics = app(MetricsCalculator::class)->from(
    usage: $context->usage(),
    budgetOverride: null,      // float|null — overrides the 'monthly_budget' setting
    contingencyOverride: null, // float|null — overrides the 'contingency_pct' setting
    now: Carbon::now(),
);

$metrics->spent;          // float  — total spent this month
$metrics->totalBudget;    // float  — full monthly budget
$metrics->baseBudget;     // float  — budget minus contingency
$metrics->contingencyAmount; // float — reserved contingency amount
$metrics->remaining;      // float  — unspent budget (floored at 0)
$metrics->percentage;     // float  — % of total budget spent
$metrics->basePercentage; // float  — % of base budget spent
$metrics->dailyAverage;   // float  — average spend per day so far
$metrics->projected;      // float  — projected month-end spend
$metrics->projectedPct;   // float  — projected % of total budget
$metrics->projectedVsBase;// float  — projected % of base budget
$metrics->calendarPct;    // float  — % of calendar days elapsed
$metrics->dayOfMonth;     // int    — current day of month
$metrics->daysInMonth;    // int    — total days in current month
$metrics->currencySymbol; // string — '$' for USD, 'EUR ' for others
```

### Pace metrics

`PaceCalculator` takes a `BudgetMetrics` instance and calculates how actual spending compares to the expected pace over working days (weekdays excluding public holidays and custom days off).

```php
use EnricoDeLazzari\ClaudeMonitor\Support\PaceCalculator;

$pace = app(PaceCalculator::class)->from($metrics, Carbon::now());

$pace->totalWorkingDays;      // int          — working days in the current month
$pace->elapsedWorkingDays;    // int          — working days elapsed so far
$pace->remainingWorkingDays;  // int          — working days left in the month
$pace->dailyTarget;           // float        — base budget ÷ total working days
$pace->expectedByNow;         // float        — what should have been spent by today
$pace->variance;              // float        — expectedByNow − spent (positive = under pace)
$pace->ratio;                 // float        — spent ÷ expectedByNow
$pace->budgetPerRemainingDay; // float        — remaining base budget ÷ remaining days
$pace->timePct;               // float        — % of working days elapsed
$pace->spendingPct;           // float        — % of base budget spent
$pace->holidays;              // list<string> — holiday dates in Y-m-d format
$pace->holidayLocale;         // string       — ISO country code used
$pace->customDaysOffCount;    // int          — custom days off this month
```

The country used for public holidays is read from the `holidays_country` setting (defaults to `it`).

### Custom days off

Add company-specific or personal days off that should be excluded from the working-day count:

```php
use EnricoDeLazzari\ClaudeMonitor\Models\DayOff;
use Illuminate\Support\Carbon;

DayOff::create(['date' => '2026-08-15', 'note' => 'Company closure']);

// Query days off for a given month
DayOff::forMonth(Carbon::now())->get();
```

Past records are automatically pruned via Laravel's `MassPrunable`. Schedule the pruning command in your application:

```php
// bootstrap/app.php (Laravel 11+)
Schedule::command('model:prune')->daily();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Enrico De Lazzari](https://github.com/enricodelazzari)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
