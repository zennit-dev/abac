<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class ConfigurationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/abac.php', 'abac');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish configuration
            $this->publishes([
                __DIR__ . '/../../config/abac.php' => config_path('abac.php'),
            ], 'abac-config');

            // Handle migrations
            $this->handleMigrations();
        }
    }

    public function handleMigrations(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $migrationPath = database_path('migrations');
        $sourcePath = __DIR__ . '/../../database/migrations/create_abac_tables.php';

        // Get existing migration, excluding backups
        $existingFile = collect(File::glob($migrationPath . '/*_create_abac_tables.php'))
            ->reject(function ($file) {
                return str_contains($file, '_backup_');
            })
            ->first();

        if ($existingFile) {
            // Only publish if content is different
            if (md5_file($existingFile) !== md5_file($sourcePath)) {
                $this->publishes([
                    $sourcePath => $existingFile,
                ], 'abac-migrations');
            }
        } else {
            // Create new migration
            $newFileName = date('Y_m_d_His') . '_create_abac_tables.php';

            $this->publishes([
                $sourcePath => database_path("migrations/{$newFileName}"),
            ], 'abac-migrations');
        }
    }
}
