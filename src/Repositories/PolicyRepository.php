<?php

namespace zennit\ABAC\Repositories;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use zennit\ABAC\Models\Policy;

readonly class PolicyRepository
{
    public function __construct(
        protected CacheRepository $cache
    ) {}

    /**
     * Get policies for a specific resource
     */
    public function getPoliciesFor(string $resource): Collection
    {
        return $this->getPoliciesQueryFor($resource)->get();
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
