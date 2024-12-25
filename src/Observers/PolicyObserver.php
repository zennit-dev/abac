<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\Policy;

class PolicyObserver
{
    /**
     * Handle the Policy "created" event.
     * Invalidates and rewarms cache when a new policy is created.
     *
     * @param Policy $policy The created policy
     */
    public function created(Policy $policy): void
    {
        $this->invalidateCache($policy);
    }

    /**
     * Handle the Policy "updated" event.
     * Invalidates and rewarms cache when a policy is updated.
     *
     * @param Policy $policy The updated policy
     */
    public function updated(Policy $policy): void
    {
        $this->invalidateCache($policy);
    }

    /**
     * Handle the Policy "deleted" event.
     * Invalidates and rewarms cache when a policy is deleted.
     *
     * @param Policy $policy The deleted policy
     */
    public function deleted(Policy $policy): void
    {
        $this->invalidateCache($policy);
    }

    /**
     * Invalidate cache for the affected resource and schedule rewarming.
     *
     * @param Policy $policy The policy that triggered the cache invalidation
     */
    private function invalidateCache(Policy $policy): void
    {
        $resource = $policy->permission->resource;
        PolicyCacheJob::dispatch('invalidate', $resource);
        PolicyCacheJob::dispatch('warm', $resource);
    }
}
