<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\ServiceProvider;

class MigrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations/create_abac_tables.php');
    }
}
