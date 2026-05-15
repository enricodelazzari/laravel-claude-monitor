<?php

namespace EnricoDeLazzari\ClaudeMonitor\DaysOff\Exceptions;

use RuntimeException;

class InvalidDaysOffRepository extends RuntimeException
{
    public static function unknown(string $name): self
    {
        return new self("DaysOff repository [{$name}] is not configured.");
    }
}
