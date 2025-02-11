<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\AbacSubjectAdditionalAttribute;

class AbacSubjectAdditionalAttributeObserver
{
    /**
     * Handle the ABACResourceAttribute "created" event.
     * Invalidates and rewarms cache when a new resource attribute is created.
     *
     * @param AbacSubjectAdditionalAttribute $attribute The created resource attribute
     */
    public function created(AbacSubjectAdditionalAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    /**
     * Handle the ABACResourceAttribute "updated" event.
     * Invalidates and rewarms cache when a resource attribute is updated.
     *
     * @param AbacSubjectAdditionalAttribute $attribute The updated resource attribute
     */
    public function updated(AbacSubjectAdditionalAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    /**
     * Handle the ABACResourceAttribute "deleted" event.
     * Invalidates and rewarms cache when a resource attribute is deleted.
     *
     * @param AbacSubjectAdditionalAttribute $attribute The deleted resource attribute
     */
    public function deleted(AbacSubjectAdditionalAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    /**
     * Invalidate cache for the affected resource and schedule rewarming.
     *
     * @param AbacSubjectAdditionalAttribute $attribute The resource attribute that triggered the cache invalidation
     */
    private function invalidateCache(AbacSubjectAdditionalAttribute $attribute): void
    {
        PolicyCacheJob::dispatch('invalidate', $attribute->resource);
        PolicyCacheJob::dispatch('warm', $attribute->resource);
    }
}
