<?php

namespace zennit\ABAC\Logging;

use Illuminate\Support\Facades\Log;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\EvaluationResult;
use zennit\ABAC\Traits\AbacHasConfigurations;

readonly class AuditLogger
{
    use AbacHasConfigurations;

    /**
     * Log an access attempt with detailed context.
     * Records both successful and failed access attempts with relevant metadata.
     *
     * @param AccessContext $context The access context containing subject and resource
     * @param EvaluationResult $result The result of the policy evaluation
     */
    public function logAccess(AccessContext $context, EvaluationResult $result): void
    {
        $message = sprintf(
            'Access %s for resource "%s" operation "%s"',
            $result->granted ? 'granted' : 'denied',
            $context->resource,
            $context->operation
        );

        $this->log('info', $message, [
            'context' => $context,
            'result' => $result,
            'type' => 'access',
        ]);
    }

    /**
     * Log a message with configurable context data.
     * Handles logging based on configuration settings.
     *
     * @param string $level The log level (info, warning, error, etc.)
     * @param string $message The log message
     * @param array $context Additional context data for the log entry
     */
    private function log(string $level, string $message, array $context = []): void
    {
        Log::channel($this->getLogChannel())->$level(
            $message,
            $this->getDetailedLogging() ? $context : []
        );
    }
}
