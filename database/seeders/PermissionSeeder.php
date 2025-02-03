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
        $abac = json_decode(file_get_contents($permission_path), true);

        foreach ($abac['permissions'] as $permission) {
            Permission::create($permission);
        }
        foreach ($abac['policies'] as $policy) {
            Policy::create($policy);
        }
        foreach ($abac['collections'] as $collection) {
            PolicyCollection::create($collection);
        }
        foreach ($abac['conditions'] as $condition) {
            CollectionCondition::create($condition);
        }
        foreach ($abac['attributes'] as $attribute) {
            ConditionAttribute::create($attribute);
        }
    }
}
