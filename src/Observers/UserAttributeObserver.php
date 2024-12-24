<?php

namespace zennit\ABAC\Observers;

use zennit\ABAC\Jobs\PolicyCacheJob;

class UserAttributeObserver
{
    public function created(): void
    {
        $this->invalidateCache();
    }

    public function updated(): void
    {
        $this->invalidateCache();
    }

    public function deleted(): void
    {
        $this->invalidateCache();
    }

    private function invalidateCache(): void
    {
        // For user attributes, we need to invalidate all caches since
        // user attributes can affect multiple resources
        PolicyCacheJob::dispatch('invalidate');
        PolicyCacheJob::dispatch();
    }
}
