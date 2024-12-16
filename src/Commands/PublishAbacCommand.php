<?php

namespace zennit\ABAC\Commands;

use Illuminate\Console\Command;

class PublishAbacCommand extends Command
{
    protected $signature = 'abac:publish {--force : Overwrite any existing files}';

    protected $description = 'Publish ABAC configuration and migrations';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'abac-config',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'abac-migrations',
            '--force' => $this->option('force'),
        ]);

        $this->info('ABAC files published successfully!');

        return self::SUCCESS;
    }
}
