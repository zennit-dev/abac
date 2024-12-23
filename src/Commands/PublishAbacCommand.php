<?php

namespace zennit\ABAC\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAbacCommand extends Command
{
    protected $signature = 'abac:publish {--force : Force the operation to run without confirmation}';

    protected $description = 'Publish ABAC configuration and migration files';

    public function handle(): void
    {
        if ($this->shouldPublishConfig()) {
            $this->publishConfig();
        }

        if ($this->shouldPublishMigrations()) {
            $this->publishMigrations();
        }

        $this->info('ABAC files published successfully!');
    }

    private function shouldPublishConfig(): bool
    {
        if ($this->option('force')) {
            return true;
        }

        if (File::exists(config_path('abac.php'))) {
            return $this->confirm(
                'The abac config file already exists. Do you want to overwrite it?',
                false
            );
        }

        return true;
    }

    private function shouldPublishMigrations(): bool
    {
        $shouldPublish = true;

        if (!$this->option('force')) {
            $existingMigration = collect(File::glob(database_path('migrations') . '/*_create_abac_tables.php'))
                ->reject(fn($file) => str_contains($file, '_backup_'))
                ->first();

            if ($existingMigration) {
                $sourcePath = __DIR__ . '/../../database/migrations/create_abac_tables.php';
                
                if (md5_file($existingMigration) === md5_file($sourcePath)) {
                    $this->info('Migration file is already up to date.');
                    $shouldPublish = false;
                } else {
                    $shouldPublish = $this->confirm(
                        'An ABAC migration file already exists and differs from the package version. Do you want to update it?',
                        false
                    );
                }
            }
        }

        return $shouldPublish;
    }

    private function publishConfig(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'abac-config',
            '--force' => true,
        ]);
    }

    private function publishMigrations(): void
    {
        $migrationPath = database_path('migrations');
        $sourcePath = __DIR__ . '/../../database/migrations/create_abac_tables.php';
        
        // Get existing migration
        $existingFile = collect(File::glob($migrationPath . '/*_create_abac_tables.php'))
            ->reject(fn($file) => str_contains($file, '_backup_'))
            ->first();

        if ($existingFile) {
            $this->call('vendor:publish', [
                '--tag' => 'abac-migrations',
                '--force' => true,
            ]);
        } else {
            $newFileName = date('Y_m_d_His') . '_create_abac_tables.php';
            $this->publishes([
                $sourcePath => database_path("migrations/{$newFileName}"),
            ], 'abac-migrations');
        }
    }
}
