<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\PolicyCondition;

class PolicyConditionObserver
{
    public function created(PolicyCondition $condition): void
    {
        $this->invalidateCache($condition);
    }

    public function updated(PolicyCondition $condition): void
    {
        $this->invalidateCache($condition);
    }

    public function deleted(PolicyCondition $condition): void
    {
        $this->invalidateCache($condition);
    }

    private function invalidateCache(PolicyCondition $condition): void
    {
        $resource = $condition->collection->policy->permission->resource;
        PolicyCacheJob::dispatch('invalidate', $resource);
        PolicyCacheJob::dispatch('warm', $resource);
    }
}
