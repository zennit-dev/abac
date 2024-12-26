<?php

namespace zennit\ABAC\Traits;

trait ZennitAbacHasConfigurations
{
    // Cache Configuration
    public function getCacheEnabled(): bool
    {
        return config('zennit_abac.cache.enabled');
    }

    public function getCacheStore(): string
    {
        return config('zennit_abac.cache.store');

    }

    public function getCacheTTL(): int
    {
        return config('zennit_abac.cache.ttl');
    }

    public function getCachePrefix(): string
    {
        return config('zennit_abac.cache.prefix');
    }

    public function getCacheWarmingEnabled(): bool
    {
        return config('zennit_abac.cache.warming.enabled');
    }

    public function getCacheWarmingSchedule(): string
    {
        return config('zennit_abac.cache.warming.schedule');
    }

    // Evaluation Configuration
    public function getStrictValidation(): bool
    {
        return config('zennit_abac.evaluation.strict_validation');
    }

    // Monitoring Configuration
    public function getLoggingEnabled(): bool
    {
        return config('zennit_abac.monitoring.logging.enabled');
    }

    public function getLogChannel(): string
    {
        $channel = config('zennit_abac.monitoring.logging.channel', 'zennit.abac');

        return config("logging.channels.$channel") ? $channel : config('logging.default');
    }

    public function getDetailedLogging(): bool
    {
        return config('zennit_abac.monitoring.logging.detailed');
    }

    public function getPerformanceLoggingEnabled(): bool
    {
        return config('zennit_abac.monitoring.performance.enabled');
    }

    public function getSlowEvaluationThreshold(): int
    {
        return config('zennit_abac.monitoring.performance.slow_threshold');
    }

    public function getEventsEnabled(): bool
    {
        return config('zennit_abac.monitoring.events.enabled');
    }

    // Operators Configuration
    public function getDisabledOperators(): array
    {
        return config('zennit_abac.operators.disabled', []);
    }

    public function getCustomOperators(): array
    {
        $operators = config('zennit_abac.operators.custom', []);

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
        return config('zennit_abac.database.user_attribute_subject_type');
    }
}
