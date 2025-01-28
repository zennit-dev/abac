<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\CollectionCondition;

class PolicyConditionObserver
{
    /**
     * Handle the CollectionCondition "created" event.
     * Invalidates and rewarms cache when a new condition is created.
     *
     * @param CollectionCondition $condition The created condition
     */
    public function created(CollectionCondition $condition): void
    {
        $this->invalidateCache($condition);
    }

    /**
     * Handle the CollectionCondition "updated" event.
     * Invalidates and rewarms cache when a condition is updated.
     *
     * @param CollectionCondition $condition The updated condition
     */
    public function updated(CollectionCondition $condition): void
    {
        $this->invalidateCache($condition);
    }

    /**
     * Handle the CollectionCondition "deleted" event.
     * Invalidates and rewarms cache when a condition is deleted.
     *
     * @param CollectionCondition $condition The deleted condition
     */
    public function deleted(CollectionCondition $condition): void
    {
        $this->invalidateCache($condition);
    }

    /**
     * Invalidate cache for the affected resource and schedule rewarming.
     *
     * @param CollectionCondition $condition The condition that triggered the cache invalidation
     */
    private function invalidateCache(CollectionCondition $condition): void
    {
        $resource = $condition->collection->policy->permission->resource;
        PolicyCacheJob::dispatch('invalidate', $resource);
        PolicyCacheJob::dispatch('warm', $resource);
    }
}
