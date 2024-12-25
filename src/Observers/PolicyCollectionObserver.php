<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\PolicyCollection;

class PolicyCollectionObserver
{
    public function created(PolicyCollection $collection): void
    {
        $this->invalidateCache($collection);
    }

    public function updated(PolicyCollection $collection): void
    {
        $this->invalidateCache($collection);
    }

    public function deleted(PolicyCollection $collection): void
    {
        $this->invalidateCache($collection);
    }

    private function invalidateCache(PolicyCollection $collection): void
    {
        $resource = $collection->policy->permission->resource;
        PolicyCacheJob::dispatch('invalidate', $resource);
        PolicyCacheJob::dispatch('warm', $resource);
    }
}
