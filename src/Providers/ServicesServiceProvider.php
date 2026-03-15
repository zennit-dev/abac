<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Contracts\ActorResolver;
use zennit\ABAC\Contracts\CacheKeyStrategy;
use zennit\ABAC\Contracts\ContextEnricher;
use zennit\ABAC\Contracts\MetricsCollector;
use zennit\ABAC\Contracts\PolicyRepository;
use zennit\ABAC\Contracts\ResourceResolver;
use zennit\ABAC\Logging\AbacAuditLogger;
use zennit\ABAC\Services\AbacAttributeLoader;
use zennit\ABAC\Services\AbacCacheManager;
use zennit\ABAC\Services\AbacPerformanceMonitor;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Services\CacheKey\DefaultCacheKeyStrategy;
use zennit\ABAC\Services\Context\NullContextEnricher;
use zennit\ABAC\Services\Evaluators\AbacChainEvaluator;
use zennit\ABAC\Services\Metrics\NullMetricsCollector;
use zennit\ABAC\Services\Permissions\PermissionManager;
use zennit\ABAC\Services\Policies\EloquentPolicyRepository;
use zennit\ABAC\Services\Resolution\DefaultActorResolver;
use zennit\ABAC\Services\Resolution\DefaultResourceResolver;

class ServicesServiceProvider extends ServiceProvider
{
    public array $singletons = [
        CacheKeyStrategy::class => DefaultCacheKeyStrategy::class,
        PolicyRepository::class => EloquentPolicyRepository::class,
        ContextEnricher::class => NullContextEnricher::class,
        MetricsCollector::class => NullMetricsCollector::class,
        ResourceResolver::class => DefaultResourceResolver::class,
        ActorResolver::class => DefaultActorResolver::class,
        AbacCacheManager::class,
        AbacPerformanceMonitor::class,
        AbacChainEvaluator::class,
        AbacAttributeLoader::class,
        AbacAuditLogger::class,
        PermissionManager::class,
        AbacService::class,
    ];

    public function register(): void
    {
        $this->app->alias(AbacService::class, 'abac');
    }
}
