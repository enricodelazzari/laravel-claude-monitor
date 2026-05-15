<?php

namespace EnricoDeLazzari\ClaudeMonitor\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @var list<string> */
    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
