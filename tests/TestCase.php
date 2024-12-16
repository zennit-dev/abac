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
            'host' => $app['config']->get('database.connections.pgsql.host', '127.0.0.1'),
            'port' => $app['config']->get('database.connections.pgsql.port', 5432),
            'database' => $app['config']->get('database.connections.pgsql.database', 'testing'),
            'username' => $app['config']->get('database.connections.pgsql.username', 'postgres'),
            'password' => $app['config']->get('database.connections.pgsql.password', 'postgres'),
        ]);

        // Load and merge ABAC config
        $app['config']->set('abac', require __DIR__ . '/../config/abac.php');
    }
}
