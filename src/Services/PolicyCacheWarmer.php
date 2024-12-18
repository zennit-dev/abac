<?php

namespace zennit\ABAC\Services;

use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Events\CacheWarmed;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Repositories\PolicyRepository;

readonly class PolicyCacheWarmer
{
    public function __construct(
        private PolicyRepository $policyRepository,
        private CacheService $cacheService,
        private ConfigurationService $config
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function warmAll(): void
    {
        if (!$this->config->getCacheWarmingEnabled()) {
            return;
        }

        $startTime = microtime(true);
        $policies = $this->policyRepository->getAllPolicies();

        /** @var Policy $policy */
        foreach ($policies as $policy) {
            $this->cacheService->remember(
                "policy:{$policy->permission->resource}:{$policy->permission->operation}",
                fn () => $policy
            );
        }

        if ($this->config->getEventsEnabled()) {
            CacheWarmed::dispatch(
                $policies->count(),
                microtime(true) - $startTime
            );
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function warmForResource(string $resource): void
    {
        $startTime = microtime(true);
        $policies = $this->policyRepository->getPoliciesByResource($resource);

        /** @var Policy $policy */
        foreach ($policies as $policy) {
            $this->cacheService->remember(
                "policy:{$resource}:{$policy->permission->operation}",
                fn () => $policy
            );
        }

        CacheWarmed::dispatch(
            $policies->count(),
            microtime(true) - $startTime,
            ['resource' => $resource]
        );
    }
}
