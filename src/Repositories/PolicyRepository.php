<?php

namespace zennit\ABAC\Repositories;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Services\ZennitAbacCacheManager;

readonly class PolicyRepository
{
    public function __construct(
        protected CacheRepository $cache,
	    protected ZennitAbacCacheManager $cacheManager
    ) {}

	/**
	 * Get policies for a specific resource and operation
	 * @throws InvalidArgumentException
	 */
    public function getPoliciesFor(string $resource, string $operation): Collection
    {
        $policies = $this->cacheManager->getPoliciesFromCache($resource, $operation);

        if (empty($policies)) {
            $policies = $this->getPoliciesQueryFor($resource)
                ->whereHas('permission', fn ($q) => $q->where('operation', $operation))
                ->with(['collections.conditions.attributes', 'permission'])
                ->get();

            $this->cacheManager->warmPolicies($policies->all());
        }

        return collect($policies);
    }

    /**
     * Get the base query builder for policies
     */
    public function getQuery(): Builder
    {
        return Policy::query()
            ->with(['permission', 'conditions.attributes']);
    }

    /**
     * Get the query builder for policies filtered by resource
     */
    public function getPoliciesQueryFor(string $resource): Builder
    {
        return $this->getQuery()
            ->whereHas('permission', fn ($query) => $query->where('resource', $resource)
            );
    }

    /**
     * Get policies grouped by resource and operation
     */
    public function getPoliciesGrouped(): Collection
    {
        return $this->getQuery()
            ->get()
            ->groupBy(function ($policy) {
                return "{$policy->permission->resource}:{$policy->permission->operation}";
            });
    }

    /**
     * Get policies for a specific resource grouped by operation
     */
    public function getPoliciesForResourceGrouped(string $resource): Collection
    {
        return $this->getPoliciesQueryFor($resource)
            ->get()
            ->groupBy(function ($policy) {
                return "{$policy->permission->resource}:{$policy->permission->operation}";
            });
    }
}
