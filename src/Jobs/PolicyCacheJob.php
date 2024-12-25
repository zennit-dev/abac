<?php

namespace zennit\ABAC\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use zennit\ABAC\Events\CacheWarmed;
use zennit\ABAC\Repositories\PolicyRepository;
use zennit\ABAC\Services\ZennitAbacCacheManager;
use zennit\ABAC\Traits\HasConfigurations;

class PolicyCacheJob implements ShouldQueue
{
    use Dispatchable;
    use HasConfigurations;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private string $action = 'warm',
        private ?string $resource = null
    ) {}

    public function handle(ZennitAbacCacheManager $cache, PolicyRepository $repository): void
    {
        if (!$this->getCacheEnabled() || !$this->getCacheWarmingEnabled()) {
            return;
        }

        $startTime = microtime(true);
        $resource = $this->resource;

        Log::info("Starting cache $this->action job" . ($resource ? " for resource: $resource" : ''));

        match ($this->action) {
            'warm' => $this->warmCache($cache, $repository),
            'invalidate' => $cache->flush(),
            default => null
        };

        if ($this->action === 'warm' && $this->getEventsEnabled()) {
            $count = $resource
                ? $repository->getPoliciesQueryFor($resource)->count()
                : $repository->getQuery()->count();

            $duration = microtime(true) - $startTime;
            $metadata = [
                'resource' => $resource,
                'action' => $this->action,
            ];

            event(new CacheWarmed($count, $duration, $metadata));
        }
    }

    private function warmCache(ZennitAbacCacheManager $cache, PolicyRepository $repository): void
    {
        $policies = $this->resource
            ? $repository->getPoliciesForResourceGrouped($this->resource)
            : $repository->getPoliciesGrouped();

        $policies->each(fn ($group) => $cache->warmPolicies($group->all()));
    }
}
