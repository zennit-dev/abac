<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\AbacPolicy;

class AbacPolicyObserver
{
    /**
     * Handle the Permission "created" event.
     * Invalidates and rewarms cache when a new permission is created.
     *
     * @param AbacPolicy $permission The created permission
     */
    public function created(AbacPolicy $permission): void
    {
        $this->invalidateCache($permission);
    }

    /**
     * Invalidate cache for the affected resource and schedule rewarming.
     *
     * @param AbacPolicy $permission The permission that triggered the cache invalidation
     */
    private function invalidateCache(AbacPolicy $permission): void
    {
        PolicyCacheJob::dispatch('invalidate', $permission->resource);
        PolicyCacheJob::dispatch('warm', $permission->resource);
    }

    /**
     * Handle the Permission "updated" event.
     * Invalidates and rewarms cache when a permission is updated.
     *
     * @param AbacPolicy $permission The updated permission
     */
    public function updated(AbacPolicy $permission): void
    {
        $this->invalidateCache($permission);
    }

    /**
     * Handle the Permission "deleted" event.
     * Invalidates and rewarms cache when a permission is deleted.
     *
     * @param AbacPolicy $permission The deleted permission
     */
    public function deleted(AbacPolicy $permission): void
    {
        $this->invalidateCache($permission);
    }
}
