<?php

namespace zennit\ABAC\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Events\CacheWarmed;
use zennit\ABAC\Repositories\PolicyRepository;
use zennit\ABAC\Services\AbacCacheManager;
use zennit\ABAC\Traits\AbacHasConfigurations;

class PolicyCacheJob implements ShouldQueue
{
    use Dispatchable;
    use AbacHasConfigurations;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $action = 'warm',
        private readonly ?string $resource = null
    ) {
    }

    /**
     * Execute the cache management job.
     * Handles both cache warming and invalidation operations.
     *
     * @param AbacCacheManager $cache The cache manager service
     * @param PolicyRepository $repository The policy repository
     *
     * @throws InvalidArgumentException If cache operations fail
     */
    public function handle(AbacCacheManager $cache, PolicyRepository $repository): void
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

    /**
     * Warm the cache with policies.
     * Loads and caches policies either for a specific resource or all resources.
     *
     * @param AbacCacheManager $cache The cache manager service
     * @param PolicyRepository $repository The policy repository
     *
     * @throws InvalidArgumentException If cache operations fail
     */
    private function warmCache(AbacCacheManager $cache, PolicyRepository $repository): void
    {
        $policies = $this->resource
            ? $repository->getPoliciesForResourceGrouped($this->resource)
            : $repository->getPoliciesGrouped();

        $policies->each(
            fn ($group) => $cache->warmPolicies($group->all())
        );
    }
}
