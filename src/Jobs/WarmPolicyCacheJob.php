<?php

namespace zennit\ABAC\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Repositories\PolicyRepository;
use zennit\ABAC\Services\CacheService;

class WarmPolicyCacheJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly ?string $resource = null
    ) {
    }

	/**
	 * @throws InvalidArgumentException
	 */
	public function handle(PolicyRepository $repository, CacheService $cache): void
    {
        if ($this->resource) {
            $policies = $repository->getPoliciesFor($this->resource, 'all');
        } else {
            $policies = $repository->all();
        }
        foreach ($policies as $policy) {
            $cache->remember("policy:{$policy->id}", fn () => $policy);
        }
    }
}
