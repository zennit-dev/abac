<?php

namespace zennit\ABAC\Commands;

use Illuminate\Console\Command;

class PublishAbacAllCommand extends Command
{
    protected $signature = 'abac:publish {--force : Force the operation to run without confirmation}';

    protected $description = 'Publish all ABAC files (config, migration, and environment variables)';

    public function handle(): void
    {
        $force = $this->option('force') ? ['--force' => true] : [];

        $this->info('Publishing ABAC files...');

        $this->call('abac:publish-config', $force);
        $this->call('abac:publish-migration', $force);
        $this->call('abac:publish-env', $force);

        $this->info('All ABAC files published successfully!');
    }
}
