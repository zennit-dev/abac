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
use zennit\ABAC\Models\PolicyCondition;
use zennit\ABAC\Services\ConditionEvaluator;
use zennit\ABAC\Services\PolicyEvaluator;
use zennit\ABAC\Tests\TestCase;
use zennit\ABAC\Validators\AccessContextValidator;

class EdgeCasesTest extends TestCase
{
    private PolicyEvaluator $policyEvaluator;

    private ConditionEvaluator $conditionEvaluator;

    /**
     * @throws UnsupportedOperatorException
     */
    public function test_handles_empty_string_values(): void
    {
        $permission = Permission::create([
            'resource' => 'posts',
            'operation' => PermissionOperations::UPDATE->value,
        ]);

        $policy = Policy::create([
            'name' => 'Test Policy',
            'permission_id' => $permission->id,
        ]);

        $condition = PolicyCondition::create([
            'operator' => PolicyOperators::EQUALS->value,
            'policy_id' => $policy->id,
        ]);

        $condition->attributes()->create([
            'attribute_name' => 'description',
            'attribute_value' => '',
        ]);

        $attributes = new AttributeCollection([
            ['attribute_name' => 'description', 'attribute_value' => ''],
        ]);

        $result = $this->conditionEvaluator->evaluate($condition, $attributes);
        $this->assertTrue($result);
    }

    /**
     * @throws UnsupportedOperatorException
     */
    public function test_throws_exception_for_invalid_subject(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Subject must have an ID');

        $invalidSubject = new stdClass(); // No ID property
        $context = new AccessContext(
            subject: $invalidSubject,
            resource: 'posts',
            operation: PermissionOperations::SHOW->value
        );

        $validator = new AccessContextValidator();
        $validator->validate($context);

        $this->policyEvaluator->evaluate($context, new AttributeCollection());
    }

    /**
     * @throws UnsupportedOperatorException
     */
    public function test_handles_malformed_policy_conditions(): void
    {
        $permission = Permission::create([
            'resource' => 'posts',
            'operation' => PermissionOperations::UPDATE->value,
        ]);

        $policy = Policy::create([
            'name' => 'Test Policy',
            'permission_id' => $permission->id,
        ]);

        $condition = PolicyCondition::create([
            'operator' => PolicyOperators::EQUALS->value,
            'policy_id' => $policy->id,
        ]);

        // No attributes added to condition
        $attributes = new AttributeCollection([
            ['attribute_name' => 'status', 'attribute_value' => 'active'],
        ]);

        $result = $this->conditionEvaluator->evaluate($condition, $attributes);
        $this->assertFalse($result);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->policyEvaluator = $this->app->make(PolicyEvaluator::class);
        $this->conditionEvaluator = $this->app->make(ConditionEvaluator::class);
    }
}
