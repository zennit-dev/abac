<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;

class AbacObjectAdditionalAttributeObserver
{
    /**
     * Handle the UserAttribute "created" event.
     * Invalidates and rewarms all caches since user attributes can affect multiple resources.
     */
    public function created(): void
    {
        $this->invalidateCache();
    }

    /**
     * Invalidate all caches and schedule complete rewarming.
     * User attributes can affect multiple resources, so we need to invalidate everything.
     */
    private function invalidateCache(): void
    {
        // For user attributes, we need to invalidate all caches since
        // user attributes can affect multiple resources
        PolicyCacheJob::dispatch('invalidate');
        PolicyCacheJob::dispatch();
    }

    /**
     * Handle the UserAttribute "updated" event.
     * Invalidates and rewarms all caches since user attributes can affect multiple resources.
     */
    public function updated(): void
    {
        $this->invalidateCache();
    }

    /**
     * Handle the UserAttribute "deleted" event.
     * Invalidates and rewarms all caches since user attributes can affect multiple resources.
     */
    public function deleted(): void
    {
        $this->invalidateCache();
    }
}
