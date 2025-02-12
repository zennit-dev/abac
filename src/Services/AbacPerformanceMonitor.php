<?php

namespace zennit\ABAC\Services;

use Illuminate\Support\Facades\Log;
use zennit\ABAC\Traits\AccessesAbacConfiguration;

readonly class AbacPerformanceMonitor
{
    use AccessesAbacConfiguration;

    private array $timers;

    public function __construct()
    {
        $this->timers = [];
    }

    /**
     * Measure the execution time of an operation.
     *
     * @param  string  $operation  The name of the operation being measured
     * @param  callable  $callback  The operation to measure
     *
     * @return mixed The result of the callback
     */
    public function measure(string $operation, callable $callback): mixed
    {
        if (!$this->getPerformanceLoggingEnabled()) {
            return $callback();
        }

        $timers = [...$this->timers, $operation => microtime(true)];
        $result = $callback();
        $duration = $this->calculateDuration($operation, $timers);

        if ($duration > $this->getSlowEvaluationThreshold()) {
            Log::channel($this->getLogChannel())
                ->warning("Performance warning: $operation took {$duration}ms");
        }

        return $result;
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
