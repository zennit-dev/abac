<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\AbacChain;

class AbacChainObserver
{
    /**
     * Handle the AbacCollectionCondition "created" event.
     * Invalidates and rewarms cache when a new condition is created.
     *
     * @param AbacChain $condition The created condition
     */
    public function created(AbacChain $condition): void
    {
        $this->invalidateCache($condition);
    }

    /**
     * Handle the AbacCollectionCondition "updated" event.
     * Invalidates and rewarms cache when a condition is updated.
     *
     * @param AbacChain $condition The updated condition
     */
    public function updated(AbacChain $condition): void
    {
        $this->invalidateCache($condition);
    }

    /**
     * Handle the AbacCollectionCondition "deleted" event.
     * Invalidates and rewarms cache when a condition is deleted.
     *
     * @param AbacChain $condition The deleted condition
     */
    public function deleted(AbacChain $condition): void
    {
        $this->invalidateCache($condition);
    }

    /**
     * Invalidate cache for the affected resource and schedule rewarming.
     *
     * @param AbacChain $condition The condition that triggered the cache invalidation
     */
    private function invalidateCache(AbacChain $condition): void
    {
        $resource = $condition->collection->policy->permission->resource;
        PolicyCacheJob::dispatch('invalidate', $resource);
        PolicyCacheJob::dispatch('warm', $resource);
    }
}
