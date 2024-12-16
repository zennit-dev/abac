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
            'host' => env('DB_HOST', 'abac_postgres'),
            'port' => env('DB_PORT', 5432),
            'database' => env('DB_DATABASE', 'testing'),
            'username' => env('DB_USERNAME', 'testing'),
            'password' => env('DB_PASSWORD', 'testing'),
        ]);

        // Load and merge ABAC config
        $app['config']->set('abac', require __DIR__ . '/../config/abac.php');
    }
}
