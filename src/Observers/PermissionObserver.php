<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\Permission;

class PermissionObserver
{
    /**
     * Handle the Permission "created" event.
     * Invalidates and rewarms cache when a new permission is created.
     *
     * @param Permission $permission The created permission
     */
    public function created(Permission $permission): void
    {
        $this->invalidateCache($permission);
    }

    /**
     * Handle the Permission "updated" event.
     * Invalidates and rewarms cache when a permission is updated.
     *
     * @param Permission $permission The updated permission
     */
    public function updated(Permission $permission): void
    {
        $this->invalidateCache($permission);
    }

    /**
     * Handle the Permission "deleted" event.
     * Invalidates and rewarms cache when a permission is deleted.
     *
     * @param Permission $permission The deleted permission
     */
    public function deleted(Permission $permission): void
    {
        $this->invalidateCache($permission);
    }

    /**
     * Invalidate cache for the affected resource and schedule rewarming.
     *
     * @param Permission $permission The permission that triggered the cache invalidation
     */
    private function invalidateCache(Permission $permission): void
    {
        PolicyCacheJob::dispatch('invalidate', $permission->resource);
        PolicyCacheJob::dispatch('warm', $permission->resource);
    }
}
