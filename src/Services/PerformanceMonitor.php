<?php

namespace zennit\ABAC\Services;

use Illuminate\Support\Facades\Log;

class PerformanceMonitor
{
    public function __construct(
        private readonly ConfigurationService $config,
        private array $timers = []
    ) {
    }

    public function measure(string $operation, callable $callback)
    {
        $this->start($operation);
        $result = $callback();
        $this->end($operation);

        return $result;
    }

    public function start(string $operation): void
    {
        $this->timers[$operation] = microtime(true);
    }

    public function end(string $operation): float
    {
        if (!isset($this->timers[$operation])) {
            return 0;
        }

        $duration = (microtime(true) - $this->timers[$operation]) * 1000;

        if ($this->config->getPerformanceLoggingEnabled() &&
            $duration > $this->config->getSlowEvaluationThreshold()) {
            Log::channel($this->config->getLogChannel())
                ->warning("Performance warning: {$operation} took {$duration}ms");
        }

        unset($this->timers[$operation]);

        return $duration;
    }
}
