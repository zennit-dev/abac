<?php

namespace zennit\ABAC\Services;

use Closure;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\NullStore;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Contracts\MetricsCollector;
use zennit\ABAC\Traits\AccessesAbacConfiguration;

readonly class AbacCacheManager
{
    use AccessesAbacConfiguration;

    private Repository $cache;

    public function __construct(private MetricsCollector $metrics)
    {
        $store = cache()->store($this->getCacheStore());
        $this->cache = $store;
        $concreteStore = $store->getStore();

        // Skip prefix setting for stores that don't support it
        if (! $concreteStore instanceof FileStore &&
            ! $concreteStore instanceof ArrayStore &&
            ! $concreteStore instanceof NullStore &&
            is_callable([$concreteStore, 'setPrefix'])
        ) {
            call_user_func([$concreteStore, 'setPrefix'], $this->getCachePrefix());
        }
    }

    /**
     * Remember an item in cache.
     *
     * @param  string  $key  The cache key
     * @param  Closure  $callback  Function that returns the value to cache
     * @return mixed The cached value
     *
     * @throws InvalidArgumentException
     */
    public function remember(string $key, Closure $callback): mixed
    {
        $this->registerCacheKey($key);

        $value = $this->cache->get($key);
        if ($value !== null) {
            $this->metrics->recordCacheLookup(true);

            return $value;
        }

        $this->metrics->recordCacheLookup(false);

        $result = $callback();
        $query = clone $result->query;
        $model = $query->getModel();

        $cacheValue = [
            'sql' => $result->query->toSql(),
            'bindings' => $result->query->getBindings(),
            'model_keys' => $query->get()->modelKeys(),
            'primary_key' => $model->getKeyName(),
            'reason' => $result->reason,
            'can' => $result->can,
        ];

        $this->cache->put($key, $cacheValue, $this->getCacheTTL());

        return $result;
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

        if (! in_array($key, $keys)) {
            $keys[] = $key;
            $this->cache->forever($registryKey, $keys);
        }
    }

    /**
     * Flush all cached items and optionally schedule cache warm-up.
     *
     * @return bool True if the cache was flushed successfully
     *
     * @throws InvalidArgumentException
     */
    public function flush(): bool
    {
        $this->logCacheOperation('flush', []);

        $keys = $this->cache->get('key_registry', []);
        $this->metrics->recordCacheFlush(count($keys));

        foreach ($keys as $key) {
            $this->cache->forget($key);
        }

        $this->cache->forget('key_registry');

        return true;
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
     * Remove an item from cache and optionally schedule cache warm-up.
     *
     * @param  string  $key  The cache key to forget
     * @return bool True if the item was removed, false otherwise
     */
    public function forget(string $key): bool
    {
        return $this->cache->forget($key);
    }

    /**
     * Get an item from cache.
     *
     * @param  string  $key  The cache key
     * @return mixed The cached value or null if not found
     *
     * @throws InvalidArgumentException
     */
    public function get(string $key): mixed
    {
        return $this->cache->get($key);
    }
}
