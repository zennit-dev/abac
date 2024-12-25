<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Logging\AuditLogger;
use zennit\ABAC\Repositories\PolicyRepository;
use zennit\ABAC\Services\ZennitAbacAttributeLoader;
use zennit\ABAC\Services\ZennitAbacCacheManager;
use zennit\ABAC\Services\ZennitAbacConditionEvaluator;
use zennit\ABAC\Services\ZennitAbacPerformanceMonitor;
use zennit\ABAC\Services\ZennitAbacPolicyEvaluator;
use zennit\ABAC\Services\ZennitAbacService;
use zennit\ABAC\Strategies\OperatorFactory;
use zennit\ABAC\Validators\AccessContextValidator;

class ServicesServiceProvider extends ServiceProvider
{
    public array $singletons = [
        ZennitAbacCacheManager::class,
        ZennitAbacPolicyEvaluator::class,
        AuditLogger::class,
        ZennitAbacPerformanceMonitor::class,
        ZennitAbacConditionEvaluator::class,
        OperatorFactory::class,
        AccessContextValidator::class,
        PolicyRepository::class,
        ZennitAbacAttributeLoader::class,
        ZennitAbacService::class,
    ];

    public function register(): void
    {
        // Register the main service with alias
        $this->app->alias(ZennitAbacService::class, 'zennit.abac');
    }
}
