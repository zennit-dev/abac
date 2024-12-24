<?php

namespace zennit\ABAC\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
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
            $policiesCount = $this->resource ?
                $repository->getPoliciesFor($this->resource)->count() :
                $repository->all()->count();
            
            $event = new CacheWarmed(
                policiesCount: $policiesCount,
                duration: microtime(true) - $startTime,
                metadata: [
                    'resource' => $this->resource,
                    'action' => $this->action,
                ]
            );
            
            event($event);
        }
    }

    private function warmCache(CacheManager $cache, PolicyRepository $repository): void
    {
        $policies = $this->resource ?
            $repository->getPoliciesFor($this->resource) :
            $repository->all();

        Collection::make($policies)
            ->chunk($this->getBatchChunkSize())
            ->each(fn ($chunk) => $cache->warmPolicies($chunk->all()));
    }
}
