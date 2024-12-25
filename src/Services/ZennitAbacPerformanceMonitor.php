<?php

namespace zennit\ABAC\Services;

use Illuminate\Support\Facades\Log;
use zennit\ABAC\Traits\ZennitAbacHasConfigurations;

readonly class ZennitAbacPerformanceMonitor
{
    use ZennitAbacHasConfigurations;

    private array $timers;

    public function __construct()
    {
        $this->timers = [];
    }

    public function measure(string $operation, callable $callback)
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

    private function calculateDuration(string $operation, array $timers): float
    {
        return isset($timers[$operation]) 
            ? (microtime(true) - $timers[$operation]) * 1000 
            : 0;
    }
}
