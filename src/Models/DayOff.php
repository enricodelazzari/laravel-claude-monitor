<?php

namespace EnricoDeLazzari\ClaudeMonitor\Models;

use EnricoDeLazzari\ClaudeMonitor\Database\Factories\DayOffFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DayOff extends Model
{
    /** @use HasFactory<DayOffFactory> */
    use HasFactory, MassPrunable;

    /** @var list<string> */
    protected $fillable = ['date', 'note'];

    /** @var array<string, string> */
    protected $casts = ['date' => 'date'];

    public function getDateFormat(): string
    {
        return 'Y-m-d';
    }

    public function prunable(): Builder
    {
        return static::where('date', '<', today()->toDateString());
    }

    /** @return Builder<DayOff> */
    public static function forMonth(Carbon $date): Builder
    {
        return static::whereYear('date', $date->year)
            ->whereMonth('date', $date->month);
    }
}
