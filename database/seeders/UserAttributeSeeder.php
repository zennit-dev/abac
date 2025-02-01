<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use zennit\ABAC\Models\UserAttribute;

class UserAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $subject = config('abac.database.user_attribute_subject_type');

        $userAttributes = [
            // System Admin
            ['subject_type' => $subject, 'subject_id' => 1, 'attribute_name' => 'role', 'attribute_value' => 'system_admin'],
            ['subject_type' => $subject, 'subject_id' => 1, 'attribute_name' => 'access_level', 'attribute_value' => 'full'],

            // Organization Admin
            ['subject_type' => $subject, 'subject_id' => 2, 'attribute_name' => 'role', 'attribute_value' => 'org_admin'],
            ['subject_type' => $subject, 'subject_id' => 2, 'attribute_name' => 'organizationId', 'attribute_value' => '1'],

            // Team Lead
            ['subject_type' => $subject, 'subject_id' => 3, 'attribute_name' => 'role', 'attribute_value' => 'team_lead'],
            ['subject_type' => $subject, 'subject_id' => 3, 'attribute_name' => 'organizationId', 'attribute_value' => '1'],
            ['subject_type' => $subject, 'subject_id' => 3, 'attribute_name' => 'teamId', 'attribute_value' => '1'],
            ['subject_type' => $subject, 'subject_id' => 3, 'attribute_name' => 'experience', 'attribute_value' => '5'],

            // Senior Team Member
            ['subject_type' => $subject, 'subject_id' => 4, 'attribute_name' => 'role', 'attribute_value' => 'team_member'],
            ['subject_type' => $subject, 'subject_id' => 4, 'attribute_name' => 'organizationId', 'attribute_value' => '1'],
            ['subject_type' => $subject, 'subject_id' => 4, 'attribute_name' => 'teamId', 'attribute_value' => '1'],
            ['subject_type' => $subject, 'subject_id' => 4, 'attribute_name' => 'experience', 'attribute_value' => '3'],

            // Junior Team Member
            ['subject_type' => $subject, 'subject_id' => 5, 'attribute_name' => 'role', 'attribute_value' => 'team_member'],
            ['subject_type' => $subject, 'subject_id' => 5, 'attribute_name' => 'organizationId', 'attribute_value' => '1'],
            ['subject_type' => $subject, 'subject_id' => 5, 'attribute_name' => 'teamId', 'attribute_value' => '1'],
            ['subject_type' => $subject, 'subject_id' => 5, 'attribute_name' => 'experience', 'attribute_value' => '1'],
        ];

        foreach ($userAttributes as $attr) {
            UserAttribute::create($attr);
        }
    }
}
