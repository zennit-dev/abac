<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\PolicyConditionAttribute;

class PolicyConditionAttributeObserver
{
    /**
     * Handle the PolicyConditionAttribute "created" event.
     * Invalidates and rewarms cache when a new attribute is created.
     *
     * @param PolicyConditionAttribute $attribute The created attribute
     */
    public function created(PolicyConditionAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    /**
     * Handle the PolicyConditionAttribute "updated" event.
     * Invalidates and rewarms cache when an attribute is updated.
     *
     * @param PolicyConditionAttribute $attribute The updated attribute
     */
    public function updated(PolicyConditionAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    /**
     * Handle the PolicyConditionAttribute "deleted" event.
     * Invalidates and rewarms cache when an attribute is deleted.
     *
     * @param PolicyConditionAttribute $attribute The deleted attribute
     */
    public function deleted(PolicyConditionAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    /**
     * Invalidate cache for the affected resource and schedule rewarming.
     *
     * @param PolicyConditionAttribute $attribute The attribute that triggered the cache invalidation
     */
    private function invalidateCache(PolicyConditionAttribute $attribute): void
    {
        $resource = $attribute->condition->policy->permission->resource;
        PolicyCacheJob::dispatch('invalidate', $resource);
        PolicyCacheJob::dispatch('warm', $resource);
    }
}
