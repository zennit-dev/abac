<?php

namespace zennit\ABAC\Services;

use zennit\ABAC\Logging\AbacAuditLogger;
use zennit\ABAC\Traits\AccessesAbacConfiguration;

readonly class AbacPerformanceMonitor
{
    use AccessesAbacConfiguration;

    private array $timers;

    public function __construct(private AbacAuditLogger $logger)
    {
        $this->timers = [];
    }

    /**
     * Measure the execution time of an operation.
     *
     * @param string $operation The name of the operation being measured
     * @param callable(): T $callback The operation to measure
     *
     * @template T
     *
     * @return array{T, float} The result of the callback and duration
     */
    public function measure(string $operation, callable $callback): array
    {
        if (!$this->getPerformanceLoggingEnabled()) {
            return [$callback(), 0.0];
        }

        $timers = [...$this->timers, $operation => microtime(true)];
        $result = $callback();
        $duration = $this->calculateDuration($operation, $timers);

        return [$result, $duration];
    }

    /**
     * Calculate the duration of an operation.
     *
     * @param  string  $operation  The operation name
     * @param  array  $timers  Array of operation start times
     *
     * @return float Duration in milliseconds
     */
    private function calculateDuration(string $operation, array $timers): float
    {
        return isset($timers[$operation])
            ? (microtime(true) - $timers[$operation]) * 1000
            : 0;
    }
}
