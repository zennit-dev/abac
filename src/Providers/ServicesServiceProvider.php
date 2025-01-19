<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Logging\AuditLogger;
use zennit\ABAC\Repositories\PolicyRepository;
use zennit\ABAC\Services\AbacAttributeLoader;
use zennit\ABAC\Services\AbacCacheManager;
use zennit\ABAC\Services\AbacPerformanceMonitor;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Services\Evaluators\AbacCollectionEvaluator;
use zennit\ABAC\Services\Evaluators\AbacPolicyEvaluator;
use zennit\ABAC\Strategies\OperatorFactory;
use zennit\ABAC\Validators\AccessContextValidator;

class ServicesServiceProvider extends ServiceProvider
{
    public array $singletons = [
        AbacCacheManager::class,
        AbacPolicyEvaluator::class,
        AuditLogger::class,
        AbacPerformanceMonitor::class,
        AbacCollectionEvaluator::class,
        OperatorFactory::class,
        AccessContextValidator::class,
        PolicyRepository::class,
        AbacAttributeLoader::class,
        AbacService::class,
    ];

    public function register(): void
    {
        // Register the main service with alias
        $this->app->alias(AbacService::class, 'abac');
    }
}
