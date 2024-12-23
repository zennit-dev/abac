<?php

namespace zennit\ABAC\Traits;

trait HasConfigurations
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

    public function getCacheWarmingEnabled(): bool
    {
        return config('abac.cache.warming.enabled');
    }

    public function getBatchChunkSize(): int
    {
        return config('abac.cache.warming.chunk_size');
    }

    // Evaluation Configuration
    public function getParallelEvaluationEnabled(): bool
    {
        return config('abac.evaluation.parallel');
    }

    public function getBatchSize(): int
    {
        return config('abac.evaluation.batch_size');
    }

    public function getEvaluationChunkSize(): int
    {
        return config('abac.evaluation.chunk_size');
    }

    public function getStrictValidation(): bool
    {
        return config('abac.evaluation.strict_validation');
    }

    // Monitoring Configuration
    public function getLoggingEnabled(): bool
    {
        return config('abac.monitoring.logging.enabled');
    }

    public function getLogChannel(): string
    {
        return config('abac.monitoring.logging.channel');
    }

    public function getDetailedLogging(): bool
    {
        return config('abac.monitoring.logging.detailed');
    }

    public function getPerformanceLoggingEnabled(): bool
    {
        return config('abac.monitoring.performance.enabled');
    }

    public function getSlowEvaluationThreshold(): int
    {
        return config('abac.monitoring.performance.slow_threshold');
    }

    public function getEventsEnabled(): bool
    {
        return config('abac.monitoring.events.enabled');
    }

    public function getAsyncEvents(): bool
    {
        return config('abac.monitoring.events.async');
    }

    // Operators Configuration
    public function getDisabledOperators(): array
    {
        return config('abac.operators.disabled', []);
    }

    public function getCustomOperators(): array
    {
        $operators = config('abac.operators.custom', []);

        // Convert to key => class format if not already
        return collect($operators)->mapWithKeys(function ($value, $key) {
            // If numeric key, use the class basename as the operator key
            if (is_numeric($key)) {
                $key = strtolower(class_basename($value));
            }

            return [$key => $value];
        })->toArray();
    }

    // Database Configuration
    public function getUserAttributeSubjectType(): string
    {
        return config('abac.database.user_attribute_subject_type');
    }
}
