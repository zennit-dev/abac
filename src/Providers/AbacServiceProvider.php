<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Services\AbacService;

class AbacServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(ConfigurationServiceProvider::class);
        $this->app->register(ServicesServiceProvider::class);
        $this->app->register(MiddlewareServiceProvider::class);
        $this->app->register(CommandServiceProvider::class);

        // Register the facade
        $this->app->bind('abac.facade', function ($app) {
            return $app->make(AbacService::class);
        });
    }
}
