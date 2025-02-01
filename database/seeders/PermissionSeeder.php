<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $basePermissions = [
            'resource' => [
                'organizations',
                'projects',
                'tasks',
                'teams',
                'users',
                'permissions',
                'policies',
                'resource_attributes',
                'user_attributes',
                'policy_collections',
                'condition_attributes',
                'condition_attributes_attributes',
            ],
            'operation' => PermissionOperations::values(),
        ];

        foreach ($basePermissions['resource'] as $resource) {
            foreach ($basePermissions['operation'] as $operation) {
                Permission::firstOrCreate([
                    'resource' => $resource,
                    'operation' => $operation,
                ]);
            }
        }
    }
}
