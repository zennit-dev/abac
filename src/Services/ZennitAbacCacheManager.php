<?php

namespace zennit\ABAC\Services;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Queue;
use Log;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Events\CacheWarmed;
use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Models\ResourceAttribute;
use zennit\ABAC\Traits\ZennitAbacHasConfigurations;

readonly class ZennitAbacCacheManager
{
    use ZennitAbacHasConfigurations;

    private const CACHE_KEYS = [
        'resource_attributes' => 'resource_attributes',
        'user_attributes' => 'user_attributes',
        'policies' => 'policies',
    ];

    public function __construct(
        private Repository $cache,
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public function rememberUserAttributes(int $userId, string $type, callable $callback): array
    {
        return $this->remember(
            self::CACHE_KEYS['user_attributes'] . ":$type:$userId",
            $callback
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function rememberResourceAttributes(string $resource, callable $callback): array
    {
        return $this->remember(
            self::CACHE_KEYS['resource_attributes'] . ":$resource",
            $callback
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function rememberPolicyEvaluation(string $key, callable $callback): mixed
    {
        return $this->remember(
            self::CACHE_KEYS['evaluations'] . ":$key",
            $callback,
            $this->getCacheTTL()
        );
    }

    public function forget(string $key): bool
    {
        $result = $this->cache->forget($this->getCachePrefix() . $key);

        if ($result && $this->getCacheWarmingEnabled()) {
            $this->scheduleWarmUp($this->extractResourceFromKey($key));
        }

        return $result;
    }

    /**
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
     */
    public function warmPolicies(array $policies): void
    {
        $startTime = microtime(true);
        $policyGroups = collect($policies)->groupBy(fn ($policy) => "{$policy->permission->resource}:{$policy->permission->operation}");

		/**
		 * @var int|null|string $key
		 * @var Policy  $groupPolicies
		 */
	    foreach ($policyGroups as $key => $groupPolicies) {
            [$resource] = explode(':', $key);

            // Cache complete policy data
            $this->remember(
                self::CACHE_KEYS['policies'] . ":$key",
                fn () => $groupPolicies->load(['collections.attributes.condition', 'permission'])->all()
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
     * @throws InvalidArgumentException
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if (!$this->getCacheEnabled()) {
            return $callback();
        }

        $fullKey = $this->getCachePrefix() . $key;

        // Register the key for later cleanup
        $this->registerCacheKey($key);

        return $this->cache->remember(
            $fullKey,
            $ttl ?? $this->getCacheTTL(),
            $callback
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function registerCacheKey(string $key): void
    {
        $registryKey = $this->getCachePrefix() . 'key_registry';
        $keys = $this->cache->get($registryKey, []);

        if (!in_array($key, $keys)) {
            $keys[] = $key;
            $this->cache->forever($registryKey, $keys);
        }
    }

    private function scheduleWarmUp(?string $resource = null): void
    {
        Queue::later(
            now()->addSeconds(5), // Small delay to allow potential batch operations to complete
            new PolicyCacheJob('warm', $resource)
        );
    }

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

    private function extractResourceFromKey(string $key): ?string
    {
        // Extract resource from cache key format "policy:resource:operation"
        if (preg_match('/^policy:([^:]+)/', $key, $matches)) {
            return $matches[1];
        }

        return null;
    }

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
     * @throws InvalidArgumentException
     */
    public function getPoliciesFromCache(string $resource, string $operation): array
    {
        $key = self::CACHE_KEYS['policies'] . ":$resource:$operation";
        return $this->cache->get($this->getCachePrefix() . $key, []);
    }
}
