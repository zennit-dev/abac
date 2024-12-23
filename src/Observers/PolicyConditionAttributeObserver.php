<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\PolicyConditionAttribute;

class PolicyConditionAttributeObserver
{
    public function created(PolicyConditionAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    public function updated(PolicyConditionAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    public function deleted(PolicyConditionAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    private function invalidateCache(PolicyConditionAttribute $attribute): void
    {
        $resource = $attribute->condition->policy->permission->resource;
        PolicyCacheJob::dispatch('invalidate', $resource);
        PolicyCacheJob::dispatch('warm', $resource);
    }
}
