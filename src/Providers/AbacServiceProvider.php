<?php

namespace zennit\ABAC\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Commands\PublishAbacCommand;
use zennit\ABAC\Contracts\AbacServiceInterface;
use zennit\ABAC\Facades\Abac;
use zennit\ABAC\Logging\AuditLogger;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Services\BatchProcessor;
use zennit\ABAC\Services\CacheService;
use zennit\ABAC\Services\ConfigurationService;
use zennit\ABAC\Services\PerformanceMonitor;
use zennit\ABAC\Services\PolicyEvaluator;

class AbacServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../../config/abac.php', 'abac');

        // Register CacheService
        $this->app->singleton(CacheService::class, function ($app) {
            return new CacheService(
                $app->make('cache.store'),
                $app->make(ConfigurationService::class)
            );
        });

        // Register AbacService
        $this->app->singleton('abac', function ($app) {
            return new AbacService(
                $app->make(PolicyEvaluator::class),
                $app->make(CacheService::class),
                $app->make(AuditLogger::class),
                $app->make(PerformanceMonitor::class),
                $app->make(ConfigurationService::class)
            );
        });

        // Register BatchProcessor
        $this->app->singleton(BatchProcessor::class, function ($app) {
            return new BatchProcessor(
                $app->make(AbacService::class),
                $app->make(ConfigurationService::class)
            );
        });

        // Auto-register the facade
        $this->app->booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('Abac', Abac::class);
        });

        $this->app->bind(AbacServiceInterface::class, AbacService::class);

        $this->app->singleton(ConfigurationService::class, function ($app) {
            return new ConfigurationService();
        });

        $this->app->singleton(AuditLogger::class, function ($app) {
            return new AuditLogger($app->make(ConfigurationService::class));
        });

        $this->app->singleton(PerformanceMonitor::class, function ($app) {
            return new PerformanceMonitor($app->make(ConfigurationService::class));
        });

    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/abac.php' => config_path('abac.php'),
            ], 'abac-config');

            // Generate timestamped migration name
            $timestamp = date('Y_m_d_His');
            $this->publishes([
                __DIR__ . '/../../database/migrations/create_abac_tables.php' => database_path("migrations/{$timestamp}_create_abac_tables.php"),
            ], 'abac-migrations');

            $this->commands([
                PublishAbacCommand::class,
            ]);
        }
    }
}
