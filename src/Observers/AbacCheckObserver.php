<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\AbacCheck;

class AbacCheckObserver
{
    /**
     * Handle the AbacCheck "created" event.
     * Invalidates and rewarms cache when a new attribute is created.
     *
     * @param AbacCheck $attribute The created attribute
     */
    public function created(AbacCheck $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    /**
     * Handle the AbacCheck "updated" event.
     * Invalidates and rewarms cache when an attribute is updated.
     *
     * @param AbacCheck $attribute The updated attribute
     */
    public function updated(AbacCheck $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    /**
     * Handle the AbacCheck "deleted" event.
     * Invalidates and rewarms cache when an attribute is deleted.
     *
     * @param AbacCheck $attribute The deleted attribute
     */
    public function deleted(AbacCheck $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    /**
     * Invalidate cache for the affected resource and schedule rewarming.
     *
     * @param AbacCheck $attribute The attribute that triggered the cache invalidation
     */
    private function invalidateCache(AbacCheck $attribute): void
    {
        $resource = $attribute->condition->policy->permission->resource;
        PolicyCacheJob::dispatch('invalidate', $resource);
        PolicyCacheJob::dispatch('warm', $resource);
    }
}
