<?php

namespace zennit\ABAC\Logging;

use Illuminate\Support\Facades\Log;
use zennit\ABAC\DTO\AccessResult;
use zennit\ABAC\Traits\AccessesAbacConfiguration;

readonly class AbacAuditLogger
{
    use AccessesAbacConfiguration;

    /**
     * Log an access attempt with detailed context.
     * Records both successful and failed access attempts with relevant metadata.
     *
     * @param AccessResult $result
     * @param string $level
     */
    public function log(AccessResult $result, string $level): void
    {
        $message = sprintf(
            'Access %s for resource "%s" operation "%s" by %s',
            $result->can ? 'granted' : 'denied',
            get_class($result->context->subject->getModel()),
            $result->context->method->value,
            get_class($result->context->object),
        );

        Log::channel($this->getLogChannel())->{$level}(
            $message,
            ['result' => $this->getDetailedLogging() ? $result : null]
        );
    }
}
