<?php

namespace zennit\ABAC\Services;

readonly class ConfigurationService
{
    // Cache Configuration
    public function getCacheEnabled(): bool
    {
        return config('abac.cache.enabled');
    }

    public function getCacheTTL(): int
    {
        return config('abac.cache.ttl');
    }

    public function getCacheTags(): array
    {
        return config('abac.cache.tags');
    }

    public function getCachePrefix(): string
    {
        return config('abac.cache.prefix');
    }

    // Logging Configuration
    public function getLoggingEnabled(): bool
    {
        return config('abac.logging.enabled');
    }

    public function getLogChannel(): string
    {
        return config('abac.logging.channel');
    }

    public function getDetailedLogging(): bool
    {
        return config('abac.logging.detailed');
    }

    public function getEventLoggingEnabled(string $event): bool
    {
        return config("abac.logging.events.{$event}");
    }

    // Performance Configuration
    public function getBatchSize(): int
    {
        return config('abac.performance.batch_size');
    }

    public function getCacheWarmingEnabled(): bool
    {
        return config('abac.performance.cache_warming_enabled');
    }

    public function getParallelEvaluationEnabled(): bool
    {
        return config('abac.performance.parallel_evaluation');
    }

    public function getPerformanceLoggingEnabled(): bool
    {
        return config('abac.performance.logging_enabled');
    }

    public function getSlowEvaluationThreshold(): int
    {
        return config('abac.performance.thresholds.slow_evaluation');
    }

    public function getBatchChunkSize(): int
    {
        return config('abac.performance.thresholds.batch_chunk_size');
    }

    // Operators Configuration
    public function getCustomOperators(): array
    {
        return config('abac.operators.custom_operators', []);
    }

    public function getDisabledOperators(): array
    {
        return config('abac.operators.disabled_operators', []);
    }

    // Validation Configuration
    public function getStrictValidation(): bool
    {
        return config('abac.validation.strict_mode');
    }

    public function getRequiredAttributes(): array
    {
        return config('abac.validation.required_attributes', []);
    }

    // Events Configuration
    public function getEventsEnabled(): bool
    {
        return config('abac.events.enabled');
    }

    public function getAsyncEvents(): bool
    {
        return config('abac.events.async');
    }

    // Tables Configuration
    public function getUserAttributesTable(): array
    {
        return config('abac.tables.user_attributes');
    }
}
