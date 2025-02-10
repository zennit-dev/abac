<?php

namespace zennit\ABAC\Repositories;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Services\AbacCacheManager;

readonly class PolicyRepository
{
    public function __construct(
        protected CacheRepository $cache,
        protected AbacCacheManager $cacheManager
    ) {
    }

    /**
     * Get policies for a specific resource and operation.
     *
     * @param  string  $resource  The resource identifier
     * @param  string  $operation  The operation name
     *
     * @throws InvalidArgumentException If cache operations fail
     * @return Collection Collection of matching policies
     */
    public function getPoliciesFor(string $resource, string $operation): Collection
    {
        $policies = $this->cacheManager->getPoliciesFromCache($resource, $operation);

        if (empty($policies)) {
            $policies = $this->getPoliciesQueryFor($resource)
                ->whereHas('permission', fn ($q) => $q->where('operation', $operation))
                ->with(['collections.conditions.attributes', 'permission'])
                ->get();

            $this->cacheManager->warmPolicies($policies);
        }

        return collect($policies);
    }

    /**
     * Get the query builder for policies filtered by resource.
     *
     * @param  string  $resource  The resource to filter by
     *
     * @return Builder Filtered query builder
     */
    public function getPoliciesQueryFor(string $resource): Builder
    {
        return $this->getQuery()
            ->whereHas(
                'permission',
                fn ($query) => $query->where('resource', $resource)
            );
    }

    /**
     * Get the base query builder for policies.
     *
     * @return Builder Query builder for policies
     */
    public function getQuery(): Builder
    {
        return Policy::query()
            ->with(['permission', 'collections.conditions.attributes']);
    }

    /**
     * Get policies grouped by resource and operation.
     *
     * @return Collection Collection of grouped policies
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
     * Get policies for a specific resource grouped by operation.
     *
     * @param  string  $resource  The resource to get policies for
     *
     * @return Collection Collection of grouped policies
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
