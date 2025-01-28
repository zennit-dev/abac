<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\ConditionAttribute;

class PolicyConditionAttributeObserver
{
    /**
     * Handle the ConditionAttribute "created" event.
     * Invalidates and rewarms cache when a new attribute is created.
     *
     * @param ConditionAttribute $attribute The created attribute
     */
    public function created(ConditionAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    /**
     * Handle the ConditionAttribute "updated" event.
     * Invalidates and rewarms cache when an attribute is updated.
     *
     * @param ConditionAttribute $attribute The updated attribute
     */
    public function updated(ConditionAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    /**
     * Handle the ConditionAttribute "deleted" event.
     * Invalidates and rewarms cache when an attribute is deleted.
     *
     * @param ConditionAttribute $attribute The deleted attribute
     */
    public function deleted(ConditionAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    /**
     * Invalidate cache for the affected resource and schedule rewarming.
     *
     * @param ConditionAttribute $attribute The attribute that triggered the cache invalidation
     */
    private function invalidateCache(ConditionAttribute $attribute): void
    {
        $resource = $attribute->condition->policy->permission->resource;
        PolicyCacheJob::dispatch('invalidate', $resource);
        PolicyCacheJob::dispatch('warm', $resource);
    }
}
