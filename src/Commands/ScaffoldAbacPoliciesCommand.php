<?php

namespace zennit\ABAC\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use zennit\ABAC\Enums\PolicyMethod;

class ScaffoldAbacPoliciesCommand extends Command
{
    protected $signature = 'abac:scaffold {--from-routes : Generate policy stubs from configured resource_patterns} {--path=stubs/abac/abac_policy_file_path.generated.json : Output path relative to resources} {--force : Overwrite output file if it exists}';

    protected $description = 'Generate ABAC policy stubs from configured route-model mappings';

    public function handle(): void
    {
        if (! $this->option('from-routes')) {
            $this->warn('Nothing to scaffold. Use --from-routes to build policy stubs from resource_patterns.');

            return;
        }

        $resourcePatterns = config('abac.middleware.resource_patterns', []);
        $models = array_values(array_unique(array_values($resourcePatterns)));

        if (empty($models)) {
            $this->warn('No resource_patterns found in config(abac.middleware.resource_patterns).');

            return;
        }

        $policies = [];
        foreach ($models as $modelClass) {
            foreach (PolicyMethod::values() as $method) {
                $policies[] = [
                    'resource' => $modelClass,
                    'method' => $method,
                    'chains' => [],
                ];
            }
        }

        $payload = [
            'policies' => $policies,
            'chains' => [],
            'checks' => [],
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'source' => 'abac.middleware.resource_patterns',
            ],
        ];

        $pathOption = $this->option('path');
        if (! is_string($pathOption) || $pathOption === '') {
            $this->error('Invalid --path option provided.');

            return;
        }

        $targetPath = resource_path($pathOption);
        if (File::exists($targetPath) && ! $this->option('force')) {
            $this->error("File already exists: $targetPath (use --force to overwrite)");

            return;
        }

        File::ensureDirectoryExists(dirname($targetPath));
        File::put(
            $targetPath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL
        );

        $this->info("ABAC scaffold generated at: $targetPath");
    }
}
