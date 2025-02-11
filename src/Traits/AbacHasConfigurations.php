<?php

namespace zennit\ABAC\Traits;

trait AbacHasConfigurations
{
    // Cache Configuration
    public function getCacheEnabled(): bool
    {
        return config('abac.cache.enabled', true);
    }

    public function getCacheStore(): string
    {
        return config('abac.cache.store', 'database');

    }

    public function getCacheTTL(): int
    {
        return config('abac.cache.ttl', 3600);
    }

    public function getCachePrefix(): string
    {
        return config('abac.cache.prefix', 'abac_');
    }

    public function getCacheWarmingEnabled(): bool
    {
        return config('abac.cache.warming.enabled', true);
    }

    public function getCacheWarmingSchedule(): string
    {
        return config('abac.cache.warming.schedule', 'hourly');
    }

    // Evaluation Configuration
    public function getStrictValidation(): bool
    {
        return config('abac.evaluation.strict_validation', true);
    }

    // Monitoring Configuration
    public function getLoggingEnabled(): bool
    {
        return config('abac.monitoring.logging.enabled', true);
    }

    public function getLogChannel(): string
    {
        $channel = config('abac.monitoring.logging.channel', 'abac');

        return config("logging.channels.$channel") ? $channel : config('logging.default');
    }

    public function getDetailedLogging(): bool
    {
        return config('abac.monitoring.logging.detailed', true);
    }

    public function getPerformanceLoggingEnabled(): bool
    {
        return config('abac.monitoring.performance.enabled', true);
    }

    public function getSlowEvaluationThreshold(): int
    {
        return config('abac.monitoring.performance.slow_threshold', 100);
    }

    public function getEventsEnabled(): bool
    {
        return config('abac.monitoring.events.enabled', true);
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
    public function getObjectAdditionalAttributes(): string
    {
        return config('abac.database.object_additional_attributes', 'App\Models\User');
    }

    // Middleware Configuration
    public function getObjectMethod(): string
    {
        return config('abac.middleware.object_method', 'user');
    }

    public function getExcludedRoutes(): array
    {
        return config('abac.middleware.excluded_routes', []);
    }

    public function getPathPatterns(): array
    {
        return config('abac.middleware.path_patterns', []);
    }
}
