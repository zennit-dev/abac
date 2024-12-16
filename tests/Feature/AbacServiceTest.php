<?php

namespace zennit\ABAC\Tests\Feature;

use Illuminate\Contracts\Container\BindingResolutionException;
use stdClass;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Enums\PolicyOperators;
use zennit\ABAC\Models\Permission;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Models\UserAttribute;
use zennit\ABAC\Tests\TestCase;

class AbacServiceTest extends TestCase
{
    /**
     * @throws BindingResolutionException
     */
    public function test_can_evaluate_simple_policy(): void
    {
        // Create a subject
        $subject = new stdClass();
        $subject->id = 1;

        // Add subject attributes
        $userAttribute = UserAttribute::create([
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'attribute_name' => 'role',
            'attribute_value' => 'admin',
        ]);

        // Create permission and policy
        $permission = Permission::create([
            'resource' => 'posts',
            'operation' => PermissionOperations::UPDATE->value,
        ]);

        $policy = Policy::create([
            'name' => 'Test Policy',
            'permission_id' => $permission->id,
        ]);

        $condition = $policy->conditions()->create([
            'operator' => PolicyOperators::EQUALS->value,
        ]);

        $condition->attributes()->create([
            'attribute_name' => 'role',
            'attribute_value' => 'admin',
        ]);

        $context = new AccessContext(
            subject: $subject,
            resource: 'posts',
            operation: PermissionOperations::UPDATE->value
        );

        $result = $this->app->make('abac')->evaluate($context);

        $this->assertTrue($result->granted);
    }
}
