<?php

namespace zennit\ABAC\Tests\Unit\Repositories;

use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Models\Permission;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Repositories\PolicyRepository;
use zennit\ABAC\Tests\TestCase;

class PolicyRepositoryTest extends TestCase
{
    private PolicyRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PolicyRepository();
    }

    public function test_retrieves_all_policies(): void
    {
        // Create test policies
        Permission::create([
            'resource' => 'posts',
            'operation' => PermissionOperations::SHOW->value,
        ])->policies()->create([
            'name' => 'Test Policy 1',
        ]);

        Permission::create([
            'resource' => 'comments',
            'operation' => PermissionOperations::UPDATE->value,
        ])->policies()->create([
            'name' => 'Test Policy 2',
        ]);

        $policies = $this->repository->all();

        $this->assertCount(2, $policies);
        $this->assertInstanceOf(Policy::class, $policies->first());
        $this->assertTrue($policies->first()->relationLoaded('permission'));
        $this->assertTrue($policies->first()->relationLoaded('conditions'));
    }
}
