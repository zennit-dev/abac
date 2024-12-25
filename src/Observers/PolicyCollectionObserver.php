<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\PolicyCollection;

class PolicyCollectionObserver
{
    /**
     * Handle the PolicyCollection "created" event.
     * Invalidates and rewarms cache when a new collection is created.
     *
     * @param PolicyCollection $collection The created collection
     */
    public function created(PolicyCollection $collection): void
    {
        $this->invalidateCache($collection);
    }

    /**
     * Handle the PolicyCollection "updated" event.
     * Invalidates and rewarms cache when a collection is updated.
     *
     * @param PolicyCollection $collection The updated collection
     */
    public function updated(PolicyCollection $collection): void
    {
        $this->invalidateCache($collection);
    }

    /**
     * Handle the PolicyCollection "deleted" event.
     * Invalidates and rewarms cache when a collection is deleted.
     *
     * @param PolicyCollection $collection The deleted collection
     */
    public function deleted(PolicyCollection $collection): void
    {
        $this->invalidateCache($collection);
    }

    /**
     * Invalidate cache for the affected resource and schedule rewarming.
     *
     * @param PolicyCollection $collection The collection that triggered the cache invalidation
     */
    private function invalidateCache(PolicyCollection $collection): void
    {
        $resource = $collection->policy->permission->resource;
        PolicyCacheJob::dispatch('invalidate', $resource);
        PolicyCacheJob::dispatch('warm', $resource);
    }
}
