<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Logging\AbacAuditLogger;
use zennit\ABAC\Services\AbacAttributeLoader;
use zennit\ABAC\Services\AbacCacheManager;
use zennit\ABAC\Services\AbacPerformanceMonitor;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Services\Evaluators\AbacChainEvaluator;

class ServicesServiceProvider extends ServiceProvider
{
    public array $singletons = [
        AbacCacheManager::class,
        AbacPerformanceMonitor::class,
        AbacChainEvaluator::class,
        AbacAttributeLoader::class,
        AbacAuditLogger::class,
        AbacService::class,
    ];

    public function register(): void
    {
        $this->app->alias(AbacService::class, 'abac');
    }
}
