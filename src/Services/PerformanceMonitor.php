<?php

namespace zennit\ABAC\Services;

use Illuminate\Support\Facades\Log;

class PerformanceMonitor
{
    private array $config;

    private array $timers = [];

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'logging_enabled' => true,
            'log_threshold' => 100, // milliseconds
        ], $config);
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

        if ($this->config['logging_enabled'] && $duration > $this->config['log_threshold']) {
            Log::warning("Performance warning: {$operation} took {$duration}ms");
        }

        unset($this->timers[$operation]);

        return $duration;
    }

    public function measure(string $operation, callable $callback)
    {
        $this->start($operation);
        $result = $callback();
        $this->end($operation);

        return $result;
    }
}
