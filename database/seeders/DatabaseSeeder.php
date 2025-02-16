<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        try {
            DB::transaction(function () {
                $start = now();
                $this->command->info('Seeding database...');

                $this->call([
                    ObjectAttributeSeeder::class,
                    SubjectAttributeSeeder::class,
                    PolicySeeder::class,
                ]);

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
}
