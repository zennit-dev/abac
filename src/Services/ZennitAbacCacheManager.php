<?php

namespace zennit\ABAC\Services;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Queue;
use Log;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Events\CacheWarmed;
use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Traits\HasConfigurations;

readonly class ZennitAbacCacheManager
{
    use HasConfigurations;

    private const CACHE_KEYS = [
        'permissions' => 'permissions:all',
        'policies' => 'policies:all',
        'policy_conditions' => 'conditions:all',
        'policy_condition_attributes' => 'condition_attributes:all',
        'resource_attributes' => 'resource_attributes:all',
        'user_attributes' => 'user_attributes:all',
    ];

    public function __construct(
        private Repository $cache,
    ) {}

    public function rememberAttributes(string $contextKey, callable $callback): AttributeCollection
    {
        return $this->remember("attributes:$contextKey", $callback);
    }

    public function rememberUserAttributes(int $userId, string $type, callable $callback): array
    {
        return $this->remember("user_attributes:$type:$userId", $callback);
    }

    public function rememberResourceAttributes(string $resource, callable $callback): array
    {
        return $this->remember("resource_attributes:$resource", $callback);
    }

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

    public function flush(): bool
    {
        $this->logCacheOperation('flush', []);

        // Flush each cache key individually
        foreach (self::CACHE_KEYS as $key) {
            $this->cache->forget($this->getCachePrefix() . $key);
        }

        if ($this->getCacheWarmingEnabled()) {
            $this->scheduleWarmUp();
        }

        return true;
    }

    public function warmPolicies(array $policies): void
    {
        $startTime = microtime(true);

        // Cache permissions
        $permissions = collect($policies)->map->permissions->unique('id');
        $this->remember(self::CACHE_KEYS['permissions'], fn () => $permissions->all());

        // Cache policies
        $this->remember(self::CACHE_KEYS['policies'], fn () => $policies);

        // Cache conditions
        $conditions = collect($policies)->flatMap->conditions;
        $this->remember(self::CACHE_KEYS['policy_conditions'], fn () => $conditions->all());

        // Cache condition attributes
        $attributes = $conditions->flatMap->attributes;
        $this->remember(self::CACHE_KEYS['policy_condition_attributes'], fn () => $attributes->all());

        $this->logCacheOperation('warm', [
            'permissions' => $permissions->count(),
            'policies' => count($policies),
            'conditions' => $conditions->count(),
            'attributes' => $attributes->count(),
        ]);

        $this->dispatchWarmingComplete(count($policies), microtime(true) - $startTime);
    }

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if (!$this->getCacheEnabled()) {
            return $callback();
        }

        return $this->cache->remember(
            $this->getCachePrefix() . $key,
            $ttl ?? $this->getCacheTTL(),
            $callback
        );
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
