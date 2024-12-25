<?php

namespace zennit\ABAC\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Services\ZennitAbacCacheManager;

/**
 * Facade for interacting with the ABAC caching system.
 * Provides methods for caching and retrieving policy evaluations,
 * user attributes, resource attributes, and managing cache warming.
 *
 * @method static array rememberUserAttributes(int $userId, string $type, callable $callback)
 * @method static array rememberResourceAttributes(string $resource, callable $callback)
 * @method static mixed rememberPolicyEvaluation(string $key, callable $callback)
 * @method static mixed remember(string $key, callable $callback, ?int $ttl = null)
 * @method static bool forget(string $key)
 * @method static bool flush()
 * @method static void warmPolicies(Collection $policies)
 * @method static array getPoliciesFromCache(string $resource, string $operation)
 *
 * @throws InvalidArgumentException When cache operations fail
 *
 * @see ZennitAbacCacheManager
 */
class AbacCache extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'zennit.abac.cache';
    }
}
