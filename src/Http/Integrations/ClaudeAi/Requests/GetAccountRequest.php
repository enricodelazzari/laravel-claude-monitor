<?php

namespace EnricoDeLazzari\ClaudeMonitor\Http\Integrations\ClaudeAi\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetAccountRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/account';
    }
}
