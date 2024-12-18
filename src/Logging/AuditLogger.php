<?php

namespace zennit\ABAC\Logging;

use Illuminate\Support\Facades\Log;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Services\ConfigurationService;

readonly class AuditLogger
{
    public function __construct(
        private ConfigurationService $config
    ) {
    }

    public function logAccess(AccessContext $context, bool $granted): void
    {
        if (!$this->config->getLoggingEnabled() ||
            !$this->config->getEventLoggingEnabled('access_evaluated')) {
            return;
        }

        $message = sprintf(
            'Access %s for resource "%s" operation "%s"',
            $granted ? 'granted' : 'denied',
            $context->resource,
            $context->operation
        );

        $logContext = $this->config->getDetailedLogging()
            ? ['context' => $context]
            : [];

        Log::channel($this->config->getLogChannel())->info($message, $logContext);
    }

    public function logPolicyChange(string $action, array $data): void
    {
        if (!$this->config->getLoggingEnabled() ||
            !$this->config->getEventLoggingEnabled('policy_changes')) {
            return;
        }

        $logContext = $this->config->getDetailedLogging() ? $data : [];
        Log::channel($this->config->getLogChannel())->info("Policy {$action}", $logContext);
    }

    public function logCacheOperation(string $operation, array $data = []): void
    {
        if (!$this->config->getLoggingEnabled() ||
            !$this->config->getEventLoggingEnabled('cache_operations')) {
            return;
        }

        $logContext = $this->config->getDetailedLogging() ? $data : [];
        Log::channel($this->config->getLogChannel())->info("Cache {$operation}", $logContext);
    }
}
