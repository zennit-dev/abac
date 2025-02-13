<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use zennit\ABAC\Models\AbacObjectAdditionalAttribute;

class ObjectAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $subject = config('abac.database.object_additional_attributes');
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
            AbacObjectAdditionalAttribute::create([...$attr, 'subject_type' => $subject]);
        }
    }
}
