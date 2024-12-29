<?php

namespace zennit\ABAC\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAbacEnvCommand extends Command
{
    protected $signature = 'zennit_abac:publish-env {--force : Force the operation to run without confirmation}';

    protected $description = 'Publish ABAC environment variables';

    private array $envVariables = [
        'ZENNIT_ABAC_CACHE_ENABLED' => true,
        'ZENNIT_ABAC_CACHE_STORE' => 'database',
        'ZENNIT_ABAC_CACHE_TTL' => 3600,
        'ZENNIT_ABAC_CACHE_PREFIX' => 'zennit_abac_',
        'ZENNIT_ABAC_CACHE_WARMING_ENABLED' => true,
        'ZENNIT_ABAC_CACHE_WARMING_SCHEDULE' => 100,
        'ZENNIT_ABAC_STRICT_VALIDATION' => true,
        'ZENNIT_ABAC_LOGGING_ENABLED' => true,
        'ZENNIT_ABAC_LOG_CHANNEL' => 'zennit.abac',
        'ZENNIT_ABAC_DETAILED_LOGGING' => false,
        'ZENNIT_ABAC_PERFORMANCE_LOGGING' => true,
        'ZENNIT_ABAC_SLOW_EVALUATION_THRESHOLD' => 100,
        'ZENNIT_ABAC_EVENTS_ENABLED' => true,
        'ZENNIT_ABAC_USER_ATTRIBUTE_SUBJECT_TYPE' => 'users',
        'ZENNIT_ABAC_SUBJECT_METHOD' => 'user',
    ];

    public function handle(): void
    {
        $filePath = $this->ask('Where would you like to save the environment variables? (provide full path, null for abort)');

        if (!$filePath) {
            $this->error('No file path provided. Aborting.');

            return;
        }

        try {
            if (!File::exists($filePath)) {
                $this->writeNewEnvFile($filePath);

                return;
            }

            if (!$this->option('force') && !$this->confirm("File $filePath exists. Do you want to check for missing ABAC variables?")) {
                return;
            }

            $this->updateExistingEnvFile($filePath);

        } catch (Exception $e) {
            $this->error('Failed to write environment variables: ' . $e->getMessage());
        }
    }

    private function writeNewEnvFile(string $filePath): void
    {
        $content = "# ABAC Configuration\n";
        foreach ($this->envVariables as $key => $value) {
            $content .= "$key=$value\n";
        }

        File::put($filePath, $content);
        $this->info('New environment file created with ABAC variables at: ' . $filePath);
    }

    private function updateExistingEnvFile(string $filePath): void
    {
        $currentEnv = File::get($filePath);
        $additions = [];

        foreach ($this->envVariables as $key => $value) {
            if (!preg_match("/^{$key}=/m", $currentEnv)) {
                $additions[] = "$key=$value";
            }
        }

        if (empty($additions)) {
            $this->info('All ABAC environment variables are already present.');

            return;
        }

        $content = "\n# Added ABAC Configuration\n" . implode("\n", $additions) . "\n";
        File::append($filePath, $content);

        $this->info('Added ' . count($additions) . ' missing ABAC variables to: ' . $filePath);
        $this->line('Added variables:');
        foreach ($additions as $addition) {
            $this->line('  ' . $addition);
        }
    }
}
