<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\Policy;

class PolicyObserver
{
    public function created(Policy $policy): void
    {
        $this->invalidateCache($policy);
    }

    public function updated(Policy $policy): void
    {
        $this->invalidateCache($policy);
    }

    public function deleted(Policy $policy): void
    {
        $this->invalidateCache($policy);
    }

    private function invalidateCache(Policy $policy): void
    {
        $resource = $policy->permission->resource;
        PolicyCacheJob::dispatch('invalidate', $resource);
        PolicyCacheJob::dispatch('warm', $resource);
    }
}
