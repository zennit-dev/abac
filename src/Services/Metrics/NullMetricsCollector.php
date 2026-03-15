<?php

namespace zennit\ABAC\Services\Metrics;

use zennit\ABAC\Contracts\MetricsCollector;

class NullMetricsCollector implements MetricsCollector
{
    public function recordEvaluation(string $operation, bool $allowed, float $duration, bool $cacheHit): void {}

    public function recordCacheLookup(bool $hit): void {}

    public function recordCacheFlush(int $keys): void {}
}
