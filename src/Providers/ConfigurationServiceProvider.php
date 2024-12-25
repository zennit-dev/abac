<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\ServiceProvider;

class ConfigurationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/zennit_abac.php', 'abac');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/zennit_abac.php' => config_path('zennit_abac.php'),
        ], 'abac-config');
    }
}
