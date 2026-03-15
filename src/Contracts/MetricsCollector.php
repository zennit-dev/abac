<?php

namespace zennit\ABAC\Contracts;

interface MetricsCollector
{
    public function recordEvaluation(string $operation, bool $allowed, float $duration, bool $cacheHit): void;

    public function recordCacheLookup(bool $hit): void;

    public function recordCacheFlush(int $keys): void;
}
