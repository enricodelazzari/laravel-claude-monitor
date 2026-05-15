<?php

namespace EnricoDeLazzari\ClaudeMonitor\Settings\Exceptions;

use RuntimeException;

class InvalidSettingsRepository extends RuntimeException
{
    public static function unknown(string $name): self
    {
        return new self("Settings repository [{$name}] is not configured.");
    }
}
