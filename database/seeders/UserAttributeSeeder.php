<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use zennit\ABAC\Models\AbacObjectAdditionalAttributes;

class UserAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $subject = config('abac.database.user_attribute_subject_type');
        $resourcePath = resource_path(config('abac.seeders.user_attribute_path'));

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
            AbacObjectAdditionalAttributes::create([...$attr, 'subject_type' => $subject]);
        }
    }
}
