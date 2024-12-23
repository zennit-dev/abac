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
        
        // Get existing migration
        $existingFile = collect(File::glob($migrationPath . '/*_create_abac_tables.php'))
            ->filter(function ($file) {
                // Exclude any file that was just created (within last minute)
                return (time() - filectime($file)) > 60;
            })
            ->first();

        // Only publish if no existing file or if forced
        if (!$existingFile) {
            $newFileName = date('Y_m_d_His') . '_create_abac_tables.php';
            
            $this->publishes([
                $sourcePath => database_path("migrations/{$newFileName}"),
            ], 'abac-migrations');
        }
    }
}
