<?php

namespace zennit\ABAC\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use zennit\ABAC\Events\CacheWarmed;
use zennit\ABAC\Repositories\PolicyRepository;
use zennit\ABAC\Services\CacheManager;
use zennit\ABAC\Traits\HasConfigurations;

class PolicyCacheJob implements ShouldQueue
{
    use Dispatchable;
    use HasConfigurations;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $action = 'warm',
        private readonly ?string $resource = null
    ) {
    }

    public function handle(CacheManager $cache, PolicyRepository $repository): void
    {
        if (!$this->getCacheEnabled() || !$this->getCacheWarmingEnabled()) {
            return;
        }

        $startTime = microtime(true);

        match ($this->action) {
            'warm' => $this->warmCache($cache, $repository),
            'invalidate' => $cache->flush(),
            default => null
        };

        if ($this->action === 'warm' && $this->getEventsEnabled()) {
            $count = $this->resource
                ? $repository->getPoliciesQueryFor($this->resource)->count()
                : $repository->getQuery()->count();

            $duration = microtime(true) - $startTime;
            $metadata = [
                'resource' => $this->resource,
                'action' => $this->action,
            ];

            event(new CacheWarmed($count, $duration, $metadata));
        }
    }

    private function warmCache(CacheManager $cache, PolicyRepository $repository): void
    {
        $policies = $this->resource
            ? $repository->getPoliciesForResourceGrouped($this->resource)
            : $repository->getPoliciesGrouped();

        $policies->each(fn($group) => $cache->warmPolicies($group->all()));
    }
}
