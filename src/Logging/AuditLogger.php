<?php

namespace zennit\ABAC\Logging;

use Illuminate\Support\Facades\Log;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\EvaluationResult;
use zennit\ABAC\Traits\ZennitAbacHasConfigurations;

readonly class AuditLogger
{
    use ZennitAbacHasConfigurations;

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

    private function log(string $level, string $message, array $context = []): void
    {
        Log::channel($this->getLogChannel())->$level(
            $message,
            $this->getDetailedLogging() ? $context : []
        );
    }
}
