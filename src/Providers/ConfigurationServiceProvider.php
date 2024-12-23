<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\ServiceProvider;

class ConfigurationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/abac.php', 'abac');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/abac.php' => config_path('abac.php'),
        ], 'abac-config');
    }
}
