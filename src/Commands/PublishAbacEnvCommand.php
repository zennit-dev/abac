<?php

namespace zennit\ABAC\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAbacEnvCommand extends Command
{
    protected $signature = 'abac:publish-env {--force : Force the operation to run without confirmation}';

    protected $description = 'Publish ABAC environment variables';

    private array $envVariables = [
        'ABAC_CACHE_ENABLED' => 'true',
        'ABAC_CACHE_TTL' => '3600',
        'ABAC_CACHE_WARMING_ENABLED' => 'true',
        'ABAC_CACHE_WARMING_CHUNK_SIZE' => '100',
        'ABAC_PARALLEL_EVALUATION' => 'false',
        'ABAC_BATCH_SIZE' => '1000',
        'ABAC_BATCH_CHUNK_SIZE' => '100',
        'ABAC_STRICT_VALIDATION' => 'true',
        'ABAC_LOGGING_ENABLED' => 'true',
        'ABAC_LOG_CHANNEL' => 'abac',
        'ABAC_DETAILED_LOGGING' => 'false',
        'ABAC_PERFORMANCE_LOGGING' => 'true',
        'ABAC_SLOW_EVALUATION_THRESHOLD' => '100',
        'ABAC_EVENTS_ENABLED' => 'true',
        'ABAC_ASYNC_EVENTS' => 'false',
        'ABAC_USER_ATTRIBUTE_SUBJECT_TYPE' => 'App\\Models\\User',
    ];

    public function handle(): void
    {
        $filePath = $this->ask('Where would you like to save the environment variables? (provide full path, null for abort)');

        if (!$filePath) {
            $this->error('No file path provided. Aborting.');

            return;
        }

        try {
            $content = "# ABAC Configuration\n";
            foreach ($this->envVariables as $key => $value) {
                $content .= "$key=$value\n";
            }

            if (File::exists($filePath) && !$this->option('force')) {
                if (!$this->confirm("File $filePath already exists. Do you want to append ABAC variables?")) {
                    return;
                }
                // Append with a newline separator
                $content = "\n" . $content;
                File::append($filePath, $content);
            } else {
                File::put($filePath, $content);
            }

            $this->info('Environment variables written successfully to: ' . $filePath);
        } catch (Exception $e) {
            $this->error('Failed to write environment variables: ' . $e->getMessage());
        }
    }
}
