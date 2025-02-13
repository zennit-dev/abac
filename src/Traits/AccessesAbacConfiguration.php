<?php

namespace zennit\ABAC\Traits;

trait AccessesAbacConfiguration
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

    // Monitoring Configuration
    public function getLoggingEnabled(): bool
    {
        return config('abac.monitoring.logging.enabled', true);
    }

    public function getLogChannel(): string
    {
        return config('abac.monitoring.logging.channel') ?? config('logging.default');
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
