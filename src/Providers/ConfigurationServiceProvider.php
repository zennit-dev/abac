<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\ServiceProvider;

class ConfigurationServiceProvider extends ServiceProvider
{
    public const CONFIG_PATH = __DIR__ . '/../../config/abac.php';

    public const CONFIG_KEY = 'abac';

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, self::CONFIG_KEY);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                self::CONFIG_PATH => config_path('abac.php'),
            ], 'zennit-abac-config');
        }
    }
}
