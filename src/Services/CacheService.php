<?php

namespace zennit\ABAC\Services;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

readonly class CacheService
{
    public function __construct(protected CacheInterface $cache, protected string $prefix, protected int $ttl)
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function remember(string $key, callable $callback): mixed
    {
        if ($value = $this->get($key)) {
            return $value;
        }

        $value = $callback();
        $this->cache->set($key, $value, $this->ttl);

        return $value;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $key): mixed
    {
        return $this->cache->get($key);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function forget(string $key): void
    {
        $this->cache->delete($key);
    }

    public function flush(): void
    {
        $this->cache->flush();
    }
}
