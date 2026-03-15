<?php

namespace zennit\ABAC\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use zennit\ABAC\Support\AbacDefaults;

class PublishAbacEnvCommand extends Command
{
    protected $signature = 'abac:publish-env {--force : Force the operation to run without confirmation}';

    protected $description = 'Publish ABAC environment variables';

    /**
     * @var array<string, scalar>
     */
    private array $envVariables;

    public function __construct()
    {
        parent::__construct();

        $this->envVariables = AbacDefaults::envVariables();
    }

    public function handle(): void
    {
        $filePath = $this->ask('Where would you like to save the environment variables? (provide full path, null for abort)');

        if (! $filePath) {
            $this->error('No file path provided. Aborting.');

            return;
        }

        try {
            if (! File::exists($filePath)) {
                $this->writeNewEnvFile($filePath);

                return;
            }

            if (! $this->option('force') && ! $this->confirm("File $filePath exists. Do you want to check for missing ABAC variables?")) {
                return;
            }

            $this->updateExistingEnvFile($filePath);

        } catch (Exception $e) {
            $this->error('Failed to write environment variables: '.$e->getMessage());
        }
    }

    private function writeNewEnvFile(string $filePath): void
    {
        $content = "# ABAC Configuration\n";
        foreach ($this->envVariables as $key => $value) {
            $content .= "$key=$value\n";
        }

        File::put($filePath, $content);
        $this->info('New environment file created with ABAC variables at: '.$filePath);
    }

    /**
     * @throws FileNotFoundException
     */
    private function updateExistingEnvFile(string $filePath): void
    {
        $currentEnv = File::get($filePath);
        $additions = [];

        foreach ($this->envVariables as $key => $value) {
            if (! preg_match("/^$key=/m", $currentEnv)) {
                $additions[] = "$key=$value";
            }
        }

        if (empty($additions)) {
            $this->info('All ABAC environment variables are already present.');

            return;
        }

        $content = "\n# Added ABAC Configuration\n".implode("\n", $additions)."\n";
        File::append($filePath, $content);

        $this->info('Added '.count($additions).' missing ABAC variables to: '.$filePath);
        $this->line('Added variables:');
        foreach ($additions as $addition) {
            $this->line('  '.$addition);
        }
    }
}
