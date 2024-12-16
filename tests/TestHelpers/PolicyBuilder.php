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
            $policyCondition = $policy->conditions()->create([
                'operator' => $condition['operator'],
            ]);

            foreach ($condition['attributes'] as $attribute) {
                $policyCondition->attributes()->create([
                    'attribute_name' => $attribute['attribute_name'],
                    'attribute_value' => $attribute['attribute_value'],
                ]);
            }
        }

        return $policy;
    }
}
