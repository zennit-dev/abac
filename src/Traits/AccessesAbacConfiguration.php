<?php

namespace zennit\ABAC\Traits;

use Illuminate\Database\Eloquent\Model;
use zennit\ABAC\Support\AbacDefaults;

trait AccessesAbacConfiguration
{
    // Cache Configuration
    public function getCacheEnabled(): bool
    {
        return config('abac.cache.enabled', AbacDefaults::CACHE_ENABLED);
    }

    public function getCacheStore(): string
    {
        return config('abac.cache.store', AbacDefaults::CACHE_STORE);
    }

    public function getCacheTTL(): int
    {
        return config('abac.cache.ttl', AbacDefaults::CACHE_TTL);
    }

    public function getCachePrefix(): string
    {
        return config('abac.cache.prefix', AbacDefaults::CACHE_PREFIX);
    }

    public function getCacheIncludeContext(): bool
    {
        return config('abac.cache.include_context', AbacDefaults::CACHE_INCLUDE_CONTEXT);
    }

    // Monitoring Configuration
    public function getLoggingEnabled(): bool
    {
        return config('abac.monitoring.logging.enabled', AbacDefaults::LOGGING_ENABLED);
    }

    public function getLogChannel(): string
    {
        return config('abac.monitoring.logging.channel') ?? config('logging.default');
    }

    public function getDetailedLogging(): bool
    {
        return config('abac.monitoring.logging.detailed', AbacDefaults::DETAILED_LOGGING);
    }

    public function getPerformanceLoggingEnabled(): bool
    {
        return config('abac.monitoring.performance.enabled', AbacDefaults::PERFORMANCE_LOGGING_ENABLED);
    }

    public function getSlowEvaluationThreshold(): int
    {
        return config('abac.monitoring.performance.slow_threshold', AbacDefaults::SLOW_EVALUATION_THRESHOLD);
    }

    // Database Configuration
    public function getActorAdditionalAttributes(): string
    {
        return config('abac.database.actor_additional_attributes', AbacDefaults::ACTOR_ADDITIONAL_ATTRIBUTES);
    }

    public function getPrimaryKey(): string
    {
        return config('abac.database.primary_key', AbacDefaults::PRIMARY_KEY);
    }

    public function getFallbackPrimaryKey(): ?string
    {
        $fallback = config('abac.database.fallback_primary_key');

        return is_string($fallback) && $fallback !== '' ? $fallback : null;
    }

    /**
     * @return array<int, string>
     */
    public function getPrimaryKeyCandidates(?Model $model = null): array
    {
        $keys = [];

        if ($model instanceof Model) {
            $keys[] = $model->getKeyName();
        }

        $keys[] = $this->getPrimaryKey();

        if (! is_null($this->getFallbackPrimaryKey())) {
            $keys[] = $this->getFallbackPrimaryKey();
        }

        return array_values(array_unique(array_filter($keys)));
    }

    public function getDefaultPolicyBehavior(): string
    {
        return config('abac.policy.default_policy_behavior', AbacDefaults::DEFAULT_POLICY_BEHAVIOR);
    }

    public function shouldAllowWhenNoPolicyMatched(): bool
    {
        return $this->getDefaultPolicyBehavior() === AbacDefaults::DEFAULT_POLICY_BEHAVIOR;
    }

    // Middleware Configuration
    public function getActorMethod(): string
    {
        return config('abac.middleware.actor_method', AbacDefaults::ACTOR_METHOD);
    }

    /**
     * @return array<int, array<string, mixed>|string>
     */
    public function getExcludedRoutes(): array
    {
        return config('abac.middleware.excluded_routes', []);
    }

    /**
     * @return array<string, string>
     */
    public function getResourcePatterns(): array
    {
        return config('abac.middleware.resource_patterns', []);
    }

    public function shouldAllowIfUnmatchedRoute(): bool
    {
        return config('abac.middleware.allow_if_unmatched_route', AbacDefaults::ALLOW_IF_UNMATCHED_ROUTE);
    }
}
