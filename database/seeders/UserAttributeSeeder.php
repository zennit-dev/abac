<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use zennit\ABAC\Models\UserAttribute;

class UserAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $subject = config('abac.database.user_attribute_subject_type');
        $resourcePath = resource_path(config('abac.seeders.user_attribute_path'));
        $userAttributes = json_decode(file_get_contents($resourcePath), true);

        foreach ($userAttributes as $attr) {
            UserAttribute::create([...$attr, 'subject_type' => $subject]);
        }
    }
}
