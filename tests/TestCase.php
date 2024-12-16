<?php

namespace zennit\ABAC\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use zennit\ABAC\Providers\AbacServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            AbacServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Configure database
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'pgsql',
            'host' => config('database.connections.pgsql.host', 'abac_postgres'),
            'port' => config('database.connections.pgsql.port', 5432),
            'database' => config('database.connections.pgsql.database', 'testing'),
            'username' => config('database.connections.pgsql.username', 'testing'),
            'password' => config('database.connections.pgsql.password', 'testing'),
        ]);

        // Load and merge ABAC config
        $app['config']->set('abac', require __DIR__ . '/../config/abac.php');
    }
}
