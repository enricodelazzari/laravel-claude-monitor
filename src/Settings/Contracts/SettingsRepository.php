<?php

namespace EnricoDeLazzari\ClaudeMonitor\Settings\Contracts;

interface SettingsRepository
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): void;

    public function has(string $key): bool;

    public function forget(string $key): void;

    /** @return array<string, mixed> */
    public function all(): array;
}
