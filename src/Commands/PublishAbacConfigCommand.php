<?php

namespace zennit\ABAC\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAbacConfigCommand extends Command
{
    protected $signature = 'abac:publish-config {--force : Force the operation to run without confirmation}';

    protected $description = 'Publish ABAC configuration file';

    public function handle(): void
    {
        if ($this->shouldPublishConfig()) {
            $this->call('vendor:publish', [
                '--tag' => 'abac-config',
                '--force' => true,
            ]);
            $this->info('Config published successfully.');
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
} 