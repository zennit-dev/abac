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
            $this->call('vendor:publish', [
                '--tag' => 'abac-config',
                '--force' => true,
            ]);
            $this->info('Config published successfully.');
        }

        if ($this->shouldPublishMigrations()) {
            $sourcePath = __DIR__ . '/../../database/migrations/create_abac_tables.php';
            $existingFile = collect(File::glob(database_path('migrations') . '/*_create_abac_tables.php'))
                ->reject(fn ($file) => str_contains($file, '_backup_'))
                ->first();

            if ($existingFile) {
                File::copy($sourcePath, $existingFile);
            } else {
                $newFileName = date('Y_m_d_His') . '_create_abac_tables.php';
                File::copy($sourcePath, database_path("migrations/$newFileName"));
            }
            $this->info('Migration published successfully.');
        }
    }

    private function shouldPublishConfig(): bool
    {
        if ($this->option('force')) {
            return true;
        }

        if (File::exists(config_path('abac.php'))) {
            return $this->confirm('Config file already exists. Do you want to overwrite it?');
        }

        return true;
    }

    private function shouldPublishMigrations(): bool
    {
        if ($this->option('force')) {
            return true;
        }

        $existingFile = collect(File::glob(database_path('migrations') . '/*_create_abac_tables.php'))
            ->reject(fn ($file) => str_contains($file, '_backup_'))
            ->first();

        if ($existingFile) {
            return $this->confirm('Migration file already exists. Do you want to overwrite it?');
        }

        return true;
    }
}
