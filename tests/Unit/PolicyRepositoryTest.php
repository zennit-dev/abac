<?php

namespace zennit\ABAC\Tests\Unit;

use Illuminate\Contracts\Container\BindingResolutionException;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Enums\PolicyOperators;
use zennit\ABAC\Repositories\PolicyRepository;
use zennit\ABAC\Tests\TestCase;
use zennit\ABAC\Tests\TestHelpers\PolicyBuilder;

class PolicyRepositoryTest extends TestCase
{
    use PolicyBuilder;

    private PolicyRepository $repository;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(PolicyRepository::class);
    }

    public function test_gets_policies_for_resource_and_operation(): void
    {
        $this->createPolicy('posts', PermissionOperations::UPDATE->value, [
            [
                'operator' => PolicyOperators::EQUALS,
                'attributes' => [['attribute_name' => 'role', 'attribute_value' => 'admin']],
            ],
        ]);

        $policies = $this->repository->getPoliciesFor('posts', PermissionOperations::UPDATE->value);

        $this->assertCount(1, $policies);
        $this->assertEquals('posts', $policies->first()->permission->resource);
    }

    public function test_creates_policy_with_conditions(): void
    {
        $policy = $this->repository->create([
            'name' => 'Test Policy',
            'resource' => 'posts',
            'operation' => PermissionOperations::UPDATE->value,
        ]);

        $this->repository->createCondition(
            $policy,
            PolicyOperators::EQUALS->value,
            [
                ['name' => 'role', 'value' => 'admin'],
            ]
        );

        $this->assertDatabaseHas('policies', ['name' => 'Test Policy']);
        $this->assertDatabaseHas('policy_conditions', ['policy_id' => $policy->id]);
    }
}
