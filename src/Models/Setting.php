<?php

namespace EnricoDeLazzari\ClaudeMonitor\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @var list<string> */
    protected $fillable = ['key', 'value'];
}
