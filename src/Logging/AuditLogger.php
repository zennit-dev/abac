<?php

namespace zennit\ABAC\Logging;

use Illuminate\Support\Facades\Log;
use zennit\ABAC\DTO\AccessContext;

class AuditLogger
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'enabled' => true,
            'channel' => 'abac',
            'detailed' => false,
        ], $config);
    }

    public function logAccess(AccessContext $context, bool $granted, array $metadata = []): void
    {
        if (!$this->config['enabled']) {
            return;
        }

        $logData = [
            'subject_id' => $context->subject->id,
            'resource' => $context->resource,
            'operation' => $context->operation,
            'granted' => $granted,
            'timestamp' => now(),
        ];

        if ($this->config['detailed']) {
            $logData = array_merge($logData, $metadata);
        }

        Log::channel($this->config['channel'])->info('Access evaluation', $logData);
    }

    public function logPerformanceIssue(string $message, array $context = []): void
    {
        if (!$this->config['enabled']) {
            return;
        }

        Log::channel($this->config['channel'])->warning($message, $context);
    }
}
