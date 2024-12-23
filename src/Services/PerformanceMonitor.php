<?php

namespace zennit\ABAC\Services;

use Illuminate\Support\Facades\Log;
use zennit\ABAC\Traits\HasConfigurations;

class PerformanceMonitor
{
    use HasConfigurations;

    public function __construct(
        private array $timers = []
    ) {
    }

    public function measure(string $operation, callable $callback)
    {
        if (!$this->getPerformanceLoggingEnabled()) {
            return $callback();
        }

        $this->start($operation);
        $result = $callback();
        $this->end($operation);

        return $result;
    }

    private function start(string $operation): void
    {
        $this->timers[$operation] = microtime(true);
    }

    private function end(string $operation): float
    {
        if (!isset($this->timers[$operation])) {
            return 0;
        }

        $duration = (microtime(true) - $this->timers[$operation]) * 1000;

        if ($duration > $this->getSlowEvaluationThreshold()) {
            Log::channel($this->getLogChannel())
                ->warning("Performance warning: $operation took {$duration}ms");
        }

        unset($this->timers[$operation]);

        return $duration;
    }
}
