<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Models\ResourceAttribute;
use zennit\ABAC\Models\UserAttribute;
use zennit\ABAC\Services\AbacCacheManager;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        try {
            DB::transaction(function () {
                $start = now();
                $this->command->info('Seeding database...');

                // Run ABAC seeders with events disabled
                Model::withoutEvents(function () {
                    $this->call([
                        // Access Control Base (arbitrary dependencies on other tables)
                        UserAttributeSeeder::class,
                        ResourceAttributeSeeder::class,
                        PermissionSeeder::class,
                        PolicySeeder::class,
                        PolicyCollectionSeeder::class,
                        PolicyConditionSeeder::class,
                        PolicyConditionAttributeSeeder::class,
                    ]);

                    // Warm the ABAC cache after ABAC seeders are complete
                    $this->warmAbacCache();
                });

                $end = now();
                $this->command->info('ABAC database seeding completed successfully in ' . $start->diffForHumans($end));
            });
        } catch (Throwable $e) {
            logger()->error('Seeding failed: ' . $e->getMessage() . ' at ' . $e->getFile() . ' on line ' . $e->getLine());
            report($e);

            Artisan::call('migrate:fresh');

            $this->command->error('Database seeding failed: ' . $e->getMessage());
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function warmAbacCache(): void
    {
        $this->command->info('Warming ABAC cache...');

        $cacheManager = app(AbacCacheManager::class);

        // Cache policies and their relationships
        $cacheManager->warmPolicies(Policy::all());

        // Cache resource attributes
        $resources = ResourceAttribute::all()->groupBy('resource');
        foreach ($resources as $resource => $attributes) {
            $cacheManager->remember(
                'resource_attributes:' . $resource,
                fn () => $attributes->toArray()
            );
        }

        // Cache user attributes
        $userAttributes = UserAttribute::all()->groupBy(function ($attribute) {
            return "user_attributes:$attribute->subject_type:$attribute->subject_id";
        });
        foreach ($userAttributes as $key => $attributes) {
            $cacheManager->remember($key, fn () => $attributes->toArray());
        }

        $this->command->info('ABAC cache warmed successfully.');
    }
}
