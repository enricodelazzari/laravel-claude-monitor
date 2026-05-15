<?php

namespace EnricoDeLazzari\ClaudeMonitor\ClaudeAi\Requests;

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
