<?php

namespace zennit\ABAC\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAbacMigrationCommand extends Command
{
    protected $signature = 'abac:publish-migration {--force : Force the operation to run without confirmation}';

    protected $description = 'Publish ABAC migration files';

    public function handle(): void
    {
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
