<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use zennit\ABAC\Models\AbacObjectAdditionalAttribute;

class ObjectAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $resourcePath = resource_path(config('abac.seeders.object_attribute_path'));

        if (!file_exists($resourcePath)) {
            $this->command->error("User attribute file not found at path: $resourcePath");

            return;
        }

        $userAttributes = json_decode(file_get_contents($resourcePath), true);

        if (!is_array($userAttributes)) {
            $this->command->error('Invalid JSON structure in user attribute file.');

            return;
        }

        foreach ($userAttributes as $attr) {
            AbacObjectAdditionalAttribute::create($attr);
        }
    }
}
