<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\ResourceAttribute;

class ResourceAttributeObserver
{
    public function created(ResourceAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    public function updated(ResourceAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    public function deleted(ResourceAttribute $attribute): void
    {
        $this->invalidateCache($attribute);
    }

    private function invalidateCache(ResourceAttribute $attribute): void
    {
        PolicyCacheJob::dispatch('invalidate', $attribute->resource);
        PolicyCacheJob::dispatch('warm', $attribute->resource);
    }
}
