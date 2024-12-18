<?php

namespace zennit\ABAC\Services;

use Illuminate\Contracts\Cache\Repository;
use Psr\SimpleCache\InvalidArgumentException;

readonly class CacheService
{
    public function __construct(
        private Repository $cache,
        private ConfigurationService $config
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if (!$this->config->getCacheEnabled()) {
            return $callback();
        }

        return $this->cache->remember(
            $this->config->getCachePrefix() . $key,
            $ttl ?? $this->config->getCacheTTL(),
            $callback
        );
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function forget(string $key): bool
    {
        return $this->cache->forget($this->config->getCachePrefix() . $key);
    }

    /**
     * @param array $tags
     *
     * @return Repository
     */
    public function tags(array $tags): Repository
    {
        return $this->cache->tags(array_merge(
            $this->config->getCacheTags(),
            $tags
        ));
    }
}
