<?php

namespace EnricoDeLazzari\ClaudeMonitor\ClaudeAi;

use Saloon\Http\Connector;

class ClaudeConnector extends Connector
{
    public function __construct(private readonly string $sessionKey) {}

    public function resolveBaseUrl(): string
    {
        return 'https://claude.ai/api';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Cookie' => "sessionKey={$this->sessionKey}",
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'application/json',
        ];
    }
}
