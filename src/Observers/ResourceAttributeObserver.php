<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\ResourceAttribute;

class ResourceAttributeObserver
{
    /**
     * Handle the ResourceAttribute "created" event.
     * Invalidates and rewarms cache when a new resource attribute is created.
     *
     * @param ResourceAttribute $attribute The created resource attribute
     */
    public function created(ResourceAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    /**
     * Handle the ResourceAttribute "updated" event.
     * Invalidates and rewarms cache when a resource attribute is updated.
     *
     * @param ResourceAttribute $attribute The updated resource attribute
     */
    public function updated(ResourceAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    /**
     * Handle the ResourceAttribute "deleted" event.
     * Invalidates and rewarms cache when a resource attribute is deleted.
     *
     * @param ResourceAttribute $attribute The deleted resource attribute
     */
    public function deleted(ResourceAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    /**
     * Invalidate cache for the affected resource and schedule rewarming.
     *
     * @param ResourceAttribute $attribute The resource attribute that triggered the cache invalidation
     */
    private function invalidateCache(ResourceAttribute $attribute): void
    {
        PolicyCacheJob::dispatch('invalidate', $attribute->resource);
        PolicyCacheJob::dispatch('warm', $attribute->resource);
    }
}
