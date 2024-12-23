<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\Permission;

class PermissionObserver
{
    public function created(Permission $permission): void
    {
        $this->invalidateCache($permission);
    }

    public function updated(Permission $permission): void
    {
        $this->invalidateCache($permission);
    }

    public function deleted(Permission $permission): void
    {
        $this->invalidateCache($permission);
    }

    private function invalidateCache(Permission $permission): void
    {
        PolicyCacheJob::dispatch('invalidate', $permission->resource);
        PolicyCacheJob::dispatch('warm', $permission->resource);
    }
}
