<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Commands\PublishAbacAllCommand;
use zennit\ABAC\Commands\PublishAbacConfigCommand;
use zennit\ABAC\Commands\PublishAbacEnvCommand;
use zennit\ABAC\Commands\PublishAbacMigrationCommand;

class ZennitCommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishAbacConfigCommand::class,
                PublishAbacMigrationCommand::class,
                PublishAbacEnvCommand::class,
                PublishAbacAllCommand::class,
            ]);
        }
    }
}
