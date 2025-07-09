<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Contracts\AbacManager;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Traits\AccessesAbacConfiguration;

class AbacServiceProvider extends ServiceProvider
{
    use AccessesAbacConfiguration;

    /**
     * Register services and dependencies.
     */
    public function register(): void
    {
        $this->app->register(ConfigurationServiceProvider::class);
        $this->app->register(ServicesServiceProvider::class);
        $this->app->register(MiddlewareServiceProvider::class);
        $this->app->register(MigrationServiceProvider::class);
        $this->app->register(CommandServiceProvider::class);

        // Register the facade
        $this->app->bind(AbacManager::class, function ($app) {
            return $app->make(AbacService::class);
        });
    }
}
