<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\ServiceProvider;

class MigrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(base_path('database/migrations'));
    }
}
