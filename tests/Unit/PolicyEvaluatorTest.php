<?php

namespace zennit\ABAC\Tests\Unit;

use Illuminate\Contracts\Container\BindingResolutionException;
use stdClass;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Enums\PolicyOperators;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Models\Permission;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Services\PolicyEvaluator;
use zennit\ABAC\Tests\TestCase;

class PolicyEvaluatorTest extends TestCase
{
    private PolicyEvaluator $evaluator;

    /**
     * @throws UnsupportedOperatorException
     * @throws ValidationException
     */
    public function test_evaluates_equals_condition(): void
    {
        // Create test policy
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

        $attributes = new AttributeCollection([
            ['attribute_name' => 'role', 'attribute_value' => 'admin'],
        ]);

        $subject = new stdClass();
        $subject->id = 1;
        $context = new AccessContext(
            subject: $subject,
            resource: 'posts',
            operation: PermissionOperations::UPDATE->value
        );

        $result = $this->evaluator->evaluate($context, $attributes);

        $this->assertTrue($result->granted);
    }

    /**
     * @throws UnsupportedOperatorException
     * @throws ValidationException
     */
    public function test_evaluates_multiple_conditions(): void
    {
        $permission = Permission::create([
            'resource' => 'posts',
            'operation' => PermissionOperations::UPDATE->value,
        ]);

        $policy = Policy::create([
            'name' => 'Test Policy',
            'permission_id' => $permission->id,
        ]);

        // First condition
        $condition1 = $policy->conditions()->create([
            'operator' => PolicyOperators::EQUALS->value,
        ]);

        $condition1->attributes()->create([
            'attribute_name' => 'role',
            'attribute_value' => 'admin',
        ]);

        // Second condition
        $condition2 = $policy->conditions()->create([
            'operator' => PolicyOperators::EQUALS->value,
        ]);

        $condition2->attributes()->create([
            'attribute_name' => 'department',
            'attribute_value' => 'IT',
        ]);

        $attributes = new AttributeCollection([
            ['attribute_name' => 'role', 'attribute_value' => 'admin'],
            ['attribute_name' => 'department', 'attribute_value' => 'IT'],
        ]);

        $subject = new \stdClass();
        $subject->id = 1;
        $context = new AccessContext(
            subject: $subject,
            resource: 'posts',
            operation: PermissionOperations::UPDATE->value
        );

        $result = $this->evaluator->evaluate($context, $attributes);

        $this->assertTrue($result->granted);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = $this->app->make(PolicyEvaluator::class);
    }
}
