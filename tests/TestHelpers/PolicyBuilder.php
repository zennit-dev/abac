<?php

namespace zennit\ABAC\Tests\TestHelpers;

use zennit\ABAC\Models\Permission;
use zennit\ABAC\Models\Policy;

trait PolicyBuilder
{
    protected function createPolicy(string $resource, string $operation, array $conditions): Policy
    {
        $permission = Permission::create([
            'resource' => $resource,
            'operation' => $operation,
        ]);

        $policy = Policy::create([
            'name' => "Test Policy for {$resource} {$operation}",
            'permission_id' => $permission->id,
        ]);

        foreach ($conditions as $condition) {
            $policy->conditions()->create([
                'operator' => $condition['operator'],
                'attributes' => $condition['attributes'],
            ]);
        }

        return $policy;
    }
}
