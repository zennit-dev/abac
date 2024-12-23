<?php

namespace zennit\ABAC\Services;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Queue;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Events\CacheWarmed;
use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Traits\HasConfigurations;

readonly class CacheManager
{
    use HasConfigurations;

    public function __construct(
        private Repository $cache,
    ) {
    }

    public function rememberAttributes(string $contextKey, callable $callback): AttributeCollection
    {
        return $this->remember("attributes:{$contextKey}", $callback);
    }

    public function rememberUserAttributes(int $userId, string $type, callable $callback): array
    {
        return $this->remember("user_attributes:{$type}:{$userId}", $callback);
    }

    public function rememberResourceAttributes(string $resource, callable $callback): array
    {
        return $this->remember("resource_attributes:{$resource}", $callback);
    }

    public function rememberPolicyEvaluation(string $key, callable $callback): mixed
    {
        return $this->remember("evaluation:{$key}", $callback);
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
        $this->forget("user_attributes:{$type}:{$userId}");
    }

    public function forgetResourceAttributes(string $resource): void
    {
        $this->forget("resource_attributes:{$resource}");
    }

    public function flush(): bool
    {
        $result = $this->cache->flush();
        
        // Schedule a complete cache warm after flush
        if ($result && $this->getCacheWarmingEnabled()) {
            $this->scheduleWarmUp();
        }
        
        return $result;
    }

    public function warmPolicies(array $policies): void
    {
        if (!$this->getCacheWarmingEnabled()) {
            return;
        }

        $startTime = microtime(true);
        $warmedCount = 0;

        $chunks = array_chunk($policies, $this->getBatchChunkSize());
        foreach ($chunks as $chunk) {
            foreach ($chunk as $policy) {
                $this->remember(
                    "policy:{$policy->permission->resource}:{$policy->permission->operation}",
                    fn () => $policy
                );
                $warmedCount++;
            }
        }

        // Dispatch cache warmed event
        $this->dispatchWarmingComplete($warmedCount, microtime(true) - $startTime);

        // Schedule next warming before expiration
        $this->scheduleNextWarming();
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

    private function scheduleNextWarming(): void
    {
        if (!$this->getCacheEnabled() || !$this->getCacheWarmingEnabled()) {
            return;
        }

        // Schedule next warming 1 minute before cache expires
        $nextRun = now()->addSeconds($this->getCacheTTL() - 60);
        
        Queue::later(
            $nextRun,
            new PolicyCacheJob('warm', null, true)
        );
    }

    private function dispatchWarmingComplete(int $count, float $duration): void
    {
        if ($this->getEventsEnabled()) {
            CacheWarmed::dispatch(
                policiesCount: $count,
                duration: $duration,
                metadata: [
                    'next_warming' => now()->addSeconds($this->getCacheTTL() - 60)->toDateTimeString(),
                    'ttl' => $this->getCacheTTL(),
                ]
            );
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
}
