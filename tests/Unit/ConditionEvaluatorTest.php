<?php

namespace zennit\ABAC\Tests\Unit;

use Illuminate\Contracts\Container\BindingResolutionException;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Enums\PolicyOperators;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Models\Permission;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Services\ConditionEvaluator;
use zennit\ABAC\Tests\TestCase;

class ConditionEvaluatorTest extends TestCase
{
    private ConditionEvaluator $evaluator;

    /**
     * @throws UnsupportedOperatorException
     */
    public function test_evaluates_single_condition(): void
    {
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
            'role' => 'admin',
        ]);

        $result = $this->evaluator->evaluate($condition, $attributes);
        $this->assertTrue($result);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = $this->app->make(ConditionEvaluator::class);
    }
}
