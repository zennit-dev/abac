<?php

namespace zennit\ABAC\Services;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\NullStore;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Log;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Events\CacheWarmed;
use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Models\ResourceAttribute;
use zennit\ABAC\Traits\AbacHasConfigurations;

readonly class AbacCacheManager
{
    use AbacHasConfigurations;

    private Repository $cache;

    private const CACHE_KEYS = [
        'resource_attributes' => 'resource_attributes',
        'user_attributes' => 'user_attributes',
        'policies' => 'policies',
    ];

    public function __construct()
    {
        $store = cache()->store($this->getCacheStore());
        $this->cache = $store;
        $concreteStore = $store->getStore();

        // Skip prefix setting for stores that don't support it
        if (!$concreteStore instanceof FileStore &&
            !$concreteStore instanceof ArrayStore &&
            !$concreteStore instanceof NullStore &&
            method_exists($concreteStore, 'setPrefix')
        ) {
            $concreteStore->setPrefix($this->getCachePrefix());
        }
    }

    /**
     * Remember user attributes in cache for a specific user and type.
     *
     * @param  int  $userId  The ID of the user
     * @param  string  $type  The type of user attributes
     * @param  callable  $callback  Function that returns the attributes to cache
     *
     * @throws InvalidArgumentException
     * @return array The cached user attributes
     */
    public function rememberUserAttributes(int $userId, string $type, callable $callback): array
    {
        return $this->remember(
            self::CACHE_KEYS['user_attributes'] . ":$type:$userId",
            $callback
        );
    }

    /**
     * Remember resource attributes in cache for a specific resource.
     *
     * @param  string  $resource  The resource identifier
     * @param  callable  $callback  Function that returns the attributes to cache
     *
     * @throws InvalidArgumentException
     * @return array The cached resource attributes
     */
    public function rememberResourceAttributes(string $resource, callable $callback): array
    {
        return $this->remember(
            self::CACHE_KEYS['resource_attributes'] . ":$resource",
            $callback
        );
    }

    /**
     * Remember policy evaluation result in cache.
     *
     * @param  string  $key  The cache key for the evaluation
     * @param  callable  $callback  Function that returns the evaluation result
     *
     * @throws InvalidArgumentException
     * @return mixed The cached evaluation result
     */
    public function rememberPolicyEvaluation(string $key, callable $callback): mixed
    {
        return $this->remember(
            self::CACHE_KEYS['policies'] . ":$key",
            $callback,
            $this->getCacheTTL()
        );
    }

    /**
     * Remove an item from cache and optionally schedule cache warm-up.
     *
     * @param  string  $key  The cache key to forget
     *
     * @return bool True if the item was removed, false otherwise
     */
    public function forget(string $key): bool
    {
        $result = $this->cache->forget($this->getCachePrefix() . $key);

        if ($result && $this->getCacheWarmingEnabled()) {
            $this->scheduleWarmUp($this->extractResourceFromKey($key));
        }

        return $result;
    }

    /**
     * Flush all cached items and optionally schedule cache warm-up.
     *
     * @throws InvalidArgumentException
     * @return bool True if the cache was flushed successfully
     */
    public function flush(): bool
    {
        $this->logCacheOperation('flush', []);

        $keys = $this->cache->get($this->getCachePrefix() . 'key_registry', []);
        foreach ($keys as $key) {
            $this->cache->forget($this->getCachePrefix() . $key);
        }

        if ($this->getCacheWarmingEnabled()) {
            $this->scheduleWarmUp();
        }

        return true;
    }

    /**
     * Warm up the cache for policies and their related resource attributes.
     *
     * @param  Collection<Policy>  $policies  Collection of Policy models to cache
     *
     * @throws InvalidArgumentException
     */
    public function warmPolicies(Collection $policies): void
    {
        $startTime = microtime(true);
        $policyGroups = $policies->groupBy(fn ($policy) => "{$policy->permission->resource}:{$policy->permission->operation}");

        /**
         * @var int|null|string $key
         * @var Policy $groupPolicies
         */
        foreach ($policyGroups as $key => $groupPolicies) {
            [$resource] = explode(':', $key);

            // Cache complete policy data
            $this->remember(
                self::CACHE_KEYS['policies'] . ":$key",
                fn () => $groupPolicies->load(['collections.conditions.attributes', 'permission'])->all()
            );

            // Cache resource attributes
            $this->remember(
                self::CACHE_KEYS['resource_attributes'] . ":$resource",
                fn () => ResourceAttribute::where('resource', $resource)->get()->all()
            );
        }

        $this->logCacheOperation('warm', [
            'groups' => $policyGroups->count(),
            'policies' => count($policies),
        ]);

        $this->dispatchWarmingComplete(count($policies), microtime(true) - $startTime);
    }

    /**
     * Remember an item in cache.
     *
     * @param  string  $key  The cache key
     * @param  callable  $callback  Function that returns the value to cache
     * @param  int|null  $ttl  Time to live in seconds, null for default TTL
     *
     * @throws InvalidArgumentException
     * @return mixed The cached value
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if (!$this->getCacheEnabled()) {
            return $callback();
        }

        $this->registerCacheKey($key);

        return $this->cache->remember(
            $key,
            $ttl ?? $this->getCacheTTL(),
            $callback
        );
    }

    /**
     * Register a cache key in the key registry for cleanup tracking.
     *
     * @param  string  $key  The cache key to register
     *
     * @throws InvalidArgumentException
     */
    private function registerCacheKey(string $key): void
    {
        $registryKey = 'key_registry';
        $keys = $this->cache->get($registryKey, []);

        if (!in_array($key, $keys)) {
            $keys[] = $key;
            $this->cache->forever($registryKey, $keys);
        }
    }

    /**
     * Schedule a cache warm-up job.
     *
     * @param  string|null  $resource  Optional resource to warm up specifically
     */
    private function scheduleWarmUp(?string $resource = null): void
    {
        Queue::later(
            now()->addSeconds(5), // Small delay to allow potential batch operations to complete
            new PolicyCacheJob('warm', $resource)
        );
    }

    /**
     * Dispatch cache warming complete event.
     *
     * @param  int  $count  Number of policies warmed
     * @param  float  $duration  Time taken to warm the cache
     */
    private function dispatchWarmingComplete(int $count, float $duration): void
    {
        if ($this->getEventsEnabled()) {
            $metadata = [
                'next_warming' => now()->addSeconds($this->getCacheTTL() - 60)->toDateTimeString(),
                'ttl' => $this->getCacheTTL(),
            ];

            event(new CacheWarmed($count, $duration, $metadata));
        }
    }

    /**
     * Extract resource name from a cache key.
     *
     * @param  string  $key  The cache key
     *
     * @return string|null The extracted resource name or null if not found
     */
    private function extractResourceFromKey(string $key): ?string
    {
        // Extract resource from cache key format "policy:resource:operation"
        if (preg_match('/^policy:([^:]+)/', $key, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Log cache operation details.
     *
     * @param  string  $operation  The operation being performed (e.g., 'warm', 'flush')
     * @param  array  $counts  Array of count metrics to log
     */
    private function logCacheOperation(string $operation, array $counts): void
    {
        $message = sprintf(
            'Cache %s: %s',
            $operation,
            collect($counts)->map(fn ($count, $type) => "$type=$count")->join(', ')
        );

        Log::info($message);
    }

    /**
     * Get cached policies for a specific resource and operation.
     *
     * @param  string  $resource  The resource identifier
     * @param  string  $operation  The operation name
     *
     * @throws InvalidArgumentException
     * @return array Array of cached policies
     */
    public function getPoliciesFromCache(string $resource, string $operation): array
    {
        $key = self::CACHE_KEYS['policies'] . ":$resource:$operation";

        return $this->cache->get($this->getCachePrefix() . $key, []);
    }
}
