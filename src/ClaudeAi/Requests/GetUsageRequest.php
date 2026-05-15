<?php

namespace EnricoDeLazzari\ClaudeMonitor\ClaudeAi\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetUsageRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly string $orgId) {}

    public function resolveEndpoint(): string
    {
        return "/organizations/{$this->orgId}/usage";
    }
}
