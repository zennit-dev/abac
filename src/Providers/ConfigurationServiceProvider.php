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
        $migrationPath = database_path('migrations');
        $sourcePath = __DIR__ . '/../../database/migrations/create_abac_tables.php';
        
        $existingFile = collect(File::glob($migrationPath . '/*_create_abac_tables.php'))
            ->reject(function ($file) {
                return str_contains($file, '_backup_');
            })
            ->first();

        if ($existingFile) {
            // Only create one backup with timestamp
            $backupPath = str_replace('.php', '_backup_' . date('Y_m_d_His') . '.php', $existingFile);
            
            // Only create backup if it doesn't exist
            if (!File::exists($backupPath)) {
                File::copy($existingFile, $backupPath);
            }

            $this->publishes([
                $sourcePath => $existingFile
            ], 'abac-migrations');
        } else {
            $newFileName = date('Y_m_d_His') . '_create_abac_tables.php';
            
            $this->publishes([
                $sourcePath => database_path("migrations/{$newFileName}"),
            ], 'abac-migrations');
        }
    }
}
