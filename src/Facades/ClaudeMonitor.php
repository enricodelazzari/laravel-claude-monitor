<?php

namespace EnricoDeLazzari\ClaudeMonitor\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \EnricoDeLazzari\ClaudeMonitor\ClaudeMonitor
 */
class ClaudeMonitor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \EnricoDeLazzari\ClaudeMonitor\ClaudeMonitor::class;
    }
}
