<?php

namespace zennit\ABAC\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Contracts\AbacServiceInterface;
use zennit\ABAC\Facades\Abac;
use zennit\ABAC\Logging\AuditLogger;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Services\BatchProcessor;
use zennit\ABAC\Services\CacheService;
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
                $app->make('config')->get('abac.cache.prefix', 'abac:'),
                $app->make('config')->get('abac.cache.ttl', 3600)
            );
        });

        // Register AbacService
        $this->app->singleton('abac', function ($app) {
            return new AbacService(
                $app->make(PolicyEvaluator::class),
                $app->make(CacheService::class),
                $app->make(AuditLogger::class),
                $app->make(PerformanceMonitor::class),
                $app->make('config')->get('abac', [])  // Provide default empty array if config is missing
            );
        });

        // Register BatchProcessor
        $this->app->singleton(BatchProcessor::class, function ($app) {
            return new BatchProcessor(
                $app->make('abac'),
                $app->make('config')->get('abac.batch', [])
            );
        });

        // Auto-register the facade
        $this->app->booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('Abac', Abac::class);
        });

        $this->app->bind(AbacServiceInterface::class, AbacService::class);

    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/abac.php' => config_path('abac.php'),
            ], 'config');

            $timestamp = date('Y_m_d_His');
            
            $this->publishes([
                __DIR__.'/../database/migrations/create_abac_tables.php' => 
                    database_path("migrations/{$timestamp}_create_abac_tables.php"),
            ], 'migrations');
        }
    }
}
