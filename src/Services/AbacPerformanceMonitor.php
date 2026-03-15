<?php

namespace zennit\ABAC\Services;

use zennit\ABAC\Traits\AccessesAbacConfiguration;

readonly class AbacPerformanceMonitor
{
    use AccessesAbacConfiguration;

    public function __construct() {}

    /**
     * Measure the execution time of an operation.
     *
     * @param  callable(): T  $callback  The operation to measure
     *
     * @template T
     *
     * @return array{T, float} The result of the callback and duration
     */
    public function measure(callable $callback): array
    {
        if (! $this->getPerformanceLoggingEnabled()) {
            return [$callback(), 0.0];
        }

        $startedAt = microtime(true);
        $result = $callback();
        $duration = $this->calculateDuration($startedAt);

        return [$result, $duration];
    }

    /**
     * Calculate the duration of an operation.
     *
     * @param  float  $startedAt  The operation start time
     * @return float Duration in milliseconds
     */
    private function calculateDuration(float $startedAt): float
    {
        return (microtime(true) - $startedAt) * 1000;
    }
}
