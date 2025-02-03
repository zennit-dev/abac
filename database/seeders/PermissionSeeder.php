<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use zennit\ABAC\Models\CollectionCondition;
use zennit\ABAC\Models\ConditionAttribute;
use zennit\ABAC\Models\Permission;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Models\PolicyCollection;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permission_path = resource_path(config('abac.seeders.permission_path'));

        if (!file_exists($permission_path)) {
            $this->command->error("Permission file not found at path: $permission_path");

            return;
        }

        $abac = json_decode(file_get_contents($permission_path), true);

        if (!is_array($abac)) {
            $this->command->error('Invalid JSON structure in permission file.');

            return;
        }

        if (isset($abac['permissions']) && is_array($abac['permissions'])) {
            foreach ($abac['permissions'] as $permission) {
                Permission::create($permission);
            }
        }

        if (isset($abac['policies']) && is_array($abac['policies'])) {
            foreach ($abac['policies'] as $policy) {
                Policy::create($policy);
            }
        }

        if (isset($abac['collections']) && is_array($abac['collections'])) {
            foreach ($abac['collections'] as $collection) {
                PolicyCollection::create($collection);
            }
        }

        if (isset($abac['conditions']) && is_array($abac['conditions'])) {
            foreach ($abac['conditions'] as $condition) {
                CollectionCondition::create($condition);
            }
        }

        if (isset($abac['attributes']) && is_array($abac['attributes'])) {
            foreach ($abac['attributes'] as $attribute) {
                ConditionAttribute::create($attribute);
            }
        }
    }
}
