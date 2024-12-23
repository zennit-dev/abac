<?php

namespace zennit\ABAC\Services;

use Illuminate\Contracts\Cache\Repository;
use zennit\ABAC\Traits\HasConfigurations;

readonly class CacheManager
{
    use HasConfigurations;

    public function __construct(
        private Repository $cache,
    ) {
    }

    public function forget(string $key): bool
    {
        return $this->cache->forget($this->getCachePrefix() . $key);
    }

    public function flush(): bool
    {
        return $this->cache->flush();
    }

    public function tags(): Repository
    {
        return $this->cache->tags($this->getCacheTags());
    }

    public function warmPolicies(array $policies): void
    {
        if (!$this->getCacheWarmingEnabled()) {
            return;
        }

        $chunks = array_chunk($policies, $this->getBatchChunkSize());
        foreach ($chunks as $chunk) {
            foreach ($chunk as $policy) {
                $this->remember(
                    "policy:{$policy->permission->resource}:{$policy->permission->operation}",
                    fn () => $policy
                );
            }
        }
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
}
