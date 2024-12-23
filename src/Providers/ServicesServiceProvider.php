<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Logging\AuditLogger;
use zennit\ABAC\Repositories\PolicyRepository;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Services\AttributeLoader;
use zennit\ABAC\Services\CacheManager;
use zennit\ABAC\Services\ConditionEvaluator;
use zennit\ABAC\Services\PerformanceMonitor;
use zennit\ABAC\Services\PolicyEvaluator;
use zennit\ABAC\Strategies\OperatorFactory;
use zennit\ABAC\Validators\AccessContextValidator;

class ServicesServiceProvider extends ServiceProvider
{
    public array $singletons = [
        CacheManager::class,
        PolicyEvaluator::class,
        AuditLogger::class,
        PerformanceMonitor::class,
        ConditionEvaluator::class,
        OperatorFactory::class,
        AccessContextValidator::class,
        PolicyRepository::class,
        AttributeLoader::class,
        AbacService::class,
    ];

    public function register(): void
    {
        // Register the main service with alias
        $this->app->alias(AbacService::class, 'abac');
    }
}
