<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\PolicyCondition;

class PolicyConditionObserver
{
    /**
     * Handle the PolicyCondition "created" event.
     * Invalidates and rewarms cache when a new condition is created.
     *
     * @param PolicyCondition $condition The created condition
     */
    public function created(PolicyCondition $condition): void
    {
        $this->invalidateCache($condition);
    }

    /**
     * Handle the PolicyCondition "updated" event.
     * Invalidates and rewarms cache when a condition is updated.
     *
     * @param PolicyCondition $condition The updated condition
     */
    public function updated(PolicyCondition $condition): void
    {
        $this->invalidateCache($condition);
    }

    /**
     * Handle the PolicyCondition "deleted" event.
     * Invalidates and rewarms cache when a condition is deleted.
     *
     * @param PolicyCondition $condition The deleted condition
     */
    public function deleted(PolicyCondition $condition): void
    {
        $this->invalidateCache($condition);
    }

    /**
     * Invalidate cache for the affected resource and schedule rewarming.
     *
     * @param PolicyCondition $condition The condition that triggered the cache invalidation
     */
    private function invalidateCache(PolicyCondition $condition): void
    {
        $resource = $condition->collection->policy->permission->resource;
        PolicyCacheJob::dispatch('invalidate', $resource);
        PolicyCacheJob::dispatch('warm', $resource);
    }
}
