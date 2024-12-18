<?php

namespace zennit\ABAC\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use zennit\ABAC\Services\CacheService;
use zennit\ABAC\Services\ConfigurationService;

class InvalidatePolicyCacheJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $policyId
    ) {
    }

    /**
     * @param CacheService $cache
     * @param ConfigurationService $config
     */
    public function handle(CacheService $cache, ConfigurationService $config): void
    {
        if (!$config->getCacheEnabled()) {
            return;
        }

        $cache->forget("policy:{$this->policyId}");

        if ($config->getEventLoggingEnabled('cache_operations')) {
            $cache->tags(['policies'])->clear();
        }
    }
}
