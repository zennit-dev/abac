<?php

namespace zennit\ABAC\Traits;

trait ZennitAbacHasConfigurations
{
    // Cache Configuration
    public function getCacheEnabled(): bool
    {
        return config('zennit_abac.cache.enabled', true);
    }

    public function getCacheStore(): string
    {
        return config('zennit_abac.cache.store', 'database');

    }

    public function getCacheTTL(): int
    {
        return config('zennit_abac.cache.ttl', 3600);
    }

    public function getCachePrefix(): string
    {
        return config('zennit_abac.cache.prefix', 'zennit_abac_');
    }

    public function getCacheWarmingEnabled(): bool
    {
        return config('zennit_abac.cache.warming.enabled', true);
    }

    public function getCacheWarmingSchedule(): string
    {
        return config('zennit_abac.cache.warming.schedule', 'hourly');
    }

    // Evaluation Configuration
    public function getStrictValidation(): bool
    {
        return config('zennit_abac.evaluation.strict_validation', true);
    }

    // Monitoring Configuration
    public function getLoggingEnabled(): bool
    {
        return config('zennit_abac.monitoring.logging.enabled', true);
    }

    public function getLogChannel(): string
    {
        $channel = config('zennit_abac.monitoring.logging.channel', 'zennit.abac');

        return config("logging.channels.$channel") ? $channel : config('logging.default');
    }

    public function getDetailedLogging(): bool
    {
        return config('zennit_abac.monitoring.logging.detailed', true);
    }

    public function getPerformanceLoggingEnabled(): bool
    {
        return config('zennit_abac.monitoring.performance.enabled', true);
    }

    public function getSlowEvaluationThreshold(): int
    {
        return config('zennit_abac.monitoring.performance.slow_threshold', 100);
    }

    public function getEventsEnabled(): bool
    {
        return config('zennit_abac.monitoring.events.enabled', true);
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
        return config('zennit_abac.database.user_attribute_subject_type', 'users');
    }

    // Middleware Configuration
    public function getSubjectMethod(): string
    {
        return config('zennit_abac.middleware.subject_method', 'user');
    }

    public function getExcludedRoutes(): array
    {
        return config('zennit_abac.middleware.excluded_routes', []);
    }
}
