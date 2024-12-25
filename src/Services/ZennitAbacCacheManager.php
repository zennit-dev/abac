<?php

namespace zennit\ABAC\Services;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Queue;
use Log;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Events\CacheWarmed;
use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\ResourceAttribute;
use zennit\ABAC\Traits\HasConfigurations;

readonly class ZennitAbacCacheManager
{
    use HasConfigurations;

    private const CACHE_KEYS = [
        'permissions' => 'permissions',
        'policies' => 'policies',
        'conditions' => 'conditions',
        'condition_attributes' => 'condition_attributes',
        'resource_attributes' => 'resource_attributes',
        'user_attributes' => 'user_attributes',
    ];

    public function __construct(
        private Repository $cache,
    ) {}

	/**
	 * @throws InvalidArgumentException
	 */
	public function rememberAttributes(string $contextKey, callable $callback): AttributeCollection
    {
        return $this->remember("attributes:$contextKey", $callback);
    }

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
        return $this->remember("evaluation:$key", $callback);
    }

    public function forget(string $key): bool
    {
        $result = $this->cache->forget($this->getCachePrefix() . $key);

        if ($result && $this->getCacheWarmingEnabled()) {
            $this->scheduleWarmUp($this->extractResourceFromKey($key));
        }

        return $result;
    }

    public function forgetUserAttributes(int $userId, string $type): void
    {
        $this->forget("user_attributes:$type:$userId");
    }

    public function forgetResourceAttributes(string $resource): void
    {
        $this->forget("resource_attributes:$resource");
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
        $policyGroups = collect($policies)->groupBy(fn ($policy) => 
            "{$policy->permission->resource}:{$policy->permission->operation}"
        );

        foreach ($policyGroups as $key => $groupPolicies) {
            [$resource] = explode(':', $key);  // Only extract the resource since operation isn't used

            // Cache permissions
            $permissions = $groupPolicies->map->permission->unique('id');
            $this->remember(self::CACHE_KEYS['permissions'] . ":$key", fn () => $permissions->all());

            // Cache policies
            $this->remember(self::CACHE_KEYS['policies'] . ":$key", fn () => $groupPolicies->all());

            // Cache conditions
            $conditions = $groupPolicies->flatMap->conditions;
            $this->remember(self::CACHE_KEYS['conditions'] . ":$key", fn () => $conditions->all());

            // Cache condition attributes
            $conditionAttributes = $conditions->flatMap->attributes;
            $this->remember(self::CACHE_KEYS['condition_attributes'] . ":$key", fn () => $conditionAttributes->all());

            // Cache resource attributes for this resource
            $resourceAttributes = ResourceAttribute::where('resource', $resource)->get();
            $this->remember(self::CACHE_KEYS['resource_attributes'] . ":$resource",
                fn () => $resourceAttributes->all()
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
}
