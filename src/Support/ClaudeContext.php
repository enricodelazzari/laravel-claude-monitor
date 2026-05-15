<?php

namespace EnricoDeLazzari\ClaudeMonitor\Support;

use EnricoDeLazzari\ClaudeMonitor\Models\Setting;
use EnricoDeLazzari\ClaudeMonitor\Services\ClaudeWebService;
use RuntimeException;

class ClaudeContext
{
    private ?ClaudeWebService $service = null;

    private ?array $account = null;

    private bool $accountLoaded = false;

    private ?array $organization = null;

    private ?array $usage = null;

    private ?array $overage = null;

    private bool $overageLoaded = false;

    public function ensureSessionKey(): void
    {
        if (empty($this->resolveSessionKey())) {
            throw new RuntimeException('CLAUDE_SESSION_KEY is not set. Run: php artisan claude:auth');
        }
    }

    private function resolveSessionKey(): ?string
    {
        return Setting::get('session_key');
    }

    public function refresh(): void
    {
        $this->account = null;
        $this->accountLoaded = false;
        $this->organization = null;
        $this->usage = null;
        $this->overage = null;
        $this->overageLoaded = false;
    }

    public function service(): ClaudeWebService
    {
        if ($this->service === null) {
            $this->ensureSessionKey();
            $this->service = new ClaudeWebService($this->resolveSessionKey());
        }

        return $this->service;
    }

    public function account(): ?array
    {
        if (! $this->accountLoaded) {
            $this->account = $this->service()->tryGetAccount();
            $this->accountLoaded = true;
        }

        return $this->account;
    }

    public function organization(): array
    {
        if ($this->organization === null) {
            $this->organization = $this->service()->getOrganization();
        }

        return $this->organization;
    }

    public function orgId(): string
    {
        return $this->organization()['uuid'];
    }

    public function usage(): array
    {
        if ($this->usage === null) {
            $this->usage = $this->service()->getUsage($this->orgId());
        }

        return $this->usage;
    }

    public function overage(): ?array
    {
        if (! $this->overageLoaded) {
            $this->overage = $this->service()->tryGetOverageSpendLimit($this->orgId());
            $this->overageLoaded = true;
        }

        return $this->overage;
    }

    public function currencySymbol(): string
    {
        $currency = $this->usage()['extra_usage']['currency'] ?? 'USD';

        return $currency === 'USD' ? '$' : $currency.' ';
    }
}
