<?php

namespace zennit\ABAC\Logging;

use Illuminate\Support\Facades\Log;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\PolicyEvaluationResult;
use zennit\ABAC\Traits\HasConfigurations;

readonly class AuditLogger
{
    use HasConfigurations;

    public function logAccess(AccessContext $context, PolicyEvaluationResult $result): void
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

    private function log(string $level, string $message, array $context = []): void
    {
        Log::channel($this->getLogChannel())->$level(
            $message,
            $this->getDetailedLogging() ? $context : []
        );
    }
}
