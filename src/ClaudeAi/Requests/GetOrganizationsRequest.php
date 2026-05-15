<?php

namespace EnricoDeLazzari\ClaudeMonitor\ClaudeAi\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetOrganizationsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/organizations';
    }
}
