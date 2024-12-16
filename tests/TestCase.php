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
            'host' => 'abac-postgres',
            'port' => 5432,
            'database' => 'testing',
            'username' => 'testing',
            'password' => 'testing',
        ]);

        // Load and merge ABAC config
        $app['config']->set('abac', require __DIR__ . '/../config/abac.php');
    }
}
