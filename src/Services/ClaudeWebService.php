<?php

namespace EnricoDeLazzari\ClaudeMonitor\Services;

use EnricoDeLazzari\ClaudeMonitor\ClaudeAi\ClaudeConnector;
use EnricoDeLazzari\ClaudeMonitor\ClaudeAi\Requests\GetAccountRequest;
use EnricoDeLazzari\ClaudeMonitor\ClaudeAi\Requests\GetOrganizationsRequest;
use EnricoDeLazzari\ClaudeMonitor\ClaudeAi\Requests\GetOverageSpendLimitRequest;
use EnricoDeLazzari\ClaudeMonitor\ClaudeAi\Requests\GetUsageRequest;
use RuntimeException;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ClaudeWebService
{
    private ClaudeConnector $connector;

    public function __construct(string $sessionKey)
    {
        $this->connector = new ClaudeConnector($sessionKey);
    }

    /** Returns null if the endpoint is not accessible (403/404). */
    public function tryGetAccount(): ?array
    {
        return $this->trySend(new GetAccountRequest);
    }

    /** @return array[] */
    public function getOrganizations(): array
    {
        $organizations = $this->send(new GetOrganizationsRequest)->json();

        if (empty($organizations)) {
            throw new RuntimeException('No organizations found for this session.');
        }

        return $organizations;
    }

    /**
     * Returns the best org for billing monitoring: prefers contracted/enterprise
     * plans over personal or evaluation accounts.
     */
    public function getOrganization(): array
    {
        $orgs = $this->getOrganizations();

        $priority = ['stripe_subscription_contracted', 'stripe_subscription'];

        foreach ($priority as $billingType) {
            foreach ($orgs as $org) {
                if (($org['billing_type'] ?? null) === $billingType) {
                    return $org;
                }
            }
        }

        return $orgs[0];
    }

    public function getUsage(string $orgId): array
    {
        return $this->send(new GetUsageRequest($orgId))->json();
    }

    /** Returns null if the endpoint is not accessible (403/404). */
    public function tryGetUsage(string $orgId): ?array
    {
        return $this->trySend(new GetUsageRequest($orgId));
    }

    /** Returns null if the endpoint is not accessible (403/404). */
    public function tryGetOverageSpendLimit(string $orgId): ?array
    {
        return $this->trySend(new GetOverageSpendLimitRequest($orgId));
    }

    /**
     * @throws RuntimeException when session is expired (401)
     */
    private function send(Request $request): Response
    {
        $response = $this->connector->send($request);

        $this->guardAgainstExpiredSession($response);
        $response->throw();

        return $response;
    }

    /** Like send(), but returns null instead of throwing on 403/404. */
    private function trySend(Request $request): ?array
    {
        $response = $this->connector->send($request);

        $this->guardAgainstExpiredSession($response);

        if (in_array($response->status(), [403, 404])) {
            return null;
        }

        $response->throw();

        return $response->json();
    }

    private function guardAgainstExpiredSession(Response $response): void
    {
        if ($response->status() === 401) {
            throw new RuntimeException('Session expired or invalid. Run: php artisan claude:auth');
        }
    }
}
