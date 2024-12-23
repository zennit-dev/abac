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
        if ($this->option('force')) {
            return true;
        }

        $existingMigration = collect(File::glob(database_path('migrations') . '/*_create_abac_tables.php'))
            ->reject(function ($file) {
                return str_contains($file, '_backup_');
            })
            ->first();

        if ($existingMigration) {
            // Check if backup already exists
            $backupPath = str_replace('.php', '_backup_' . date('Y_m_d_His') . '.php', $existingMigration);
            if (File::exists($backupPath)) {
                return $this->confirm(
                    'A backup of the ABAC migration already exists. Do you want to update the migration anyway?',
                    false
                );
            }

            return $this->confirm(
                'An ABAC migration file already exists. Do you want to update it? (A backup will be created)',
                false
            );
        }

        return true;
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
        $this->call('vendor:publish', [
            '--tag' => 'abac-migrations',
            '--force' => true,
        ]);
    }
}
