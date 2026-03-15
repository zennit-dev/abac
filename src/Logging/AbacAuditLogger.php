<?php

namespace zennit\ABAC\Logging;

use Illuminate\Support\Facades\Log;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AccessResult;
use zennit\ABAC\Traits\AccessesAbacConfiguration;

readonly class AbacAuditLogger
{
    use AccessesAbacConfiguration;

    /**
     * Log an access attempt with detailed context.
     * Records both successful and failed access attempts with relevant metadata.
     */
    public function log(AccessResult $result, string $level): void
    {
        $resourceModel = $result->context->resource->getModel();
        $actor = $result->context->actor;

        $message = sprintf(
            'Access %s for resource "%s" operation "%s" by %s',
            $result->can ? 'granted' : 'denied',
            get_class($resourceModel),
            $result->context->method->value,
            get_class($actor),
        );

        Log::channel($this->getLogChannel())->{$level}(
            $message,
            ['result' => $this->getDetailedLogging() ? $result : null]
        );
    }

    public function logPolicyMiss(AccessContext $context): void
    {
        Log::channel($this->getLogChannel())->warning(
            'No matching ABAC policy for request',
            $this->buildContext($context) + ['event' => 'abac.policy_miss']
        );
    }

    public function logChainOutcome(AccessContext $context, bool $allowed, ?int $policyId, ?int $chainId, ?string $reason = null): void
    {
        $level = $allowed ? 'info' : 'warning';

        Log::channel($this->getLogChannel())->{$level}(
            'ABAC chain evaluation completed',
            $this->buildContext($context) + [
                'event' => 'abac.chain_outcome',
                'allowed' => $allowed,
                'policy_id' => $policyId,
                'chain_id' => $chainId,
                'reason' => $reason,
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContext(AccessContext $context): array
    {
        $resourceModel = $context->resource->getModel();
        $actor = $context->actor;

        $base = [
            'method' => $context->method->value,
            'resource_model' => get_class($resourceModel),
            'actor_model' => get_class($actor),
            'actor_key' => $actor->getKey(),
        ];

        if (! $this->getDetailedLogging()) {
            return $base;
        }

        return $base + ['environment' => $context->environment];
    }
}
