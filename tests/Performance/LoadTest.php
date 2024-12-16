<?php

namespace zennit\ABAC\Tests\Performance;

use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\SimpleCache\InvalidArgumentException;
use stdClass;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Enums\PolicyOperators;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Tests\TestCase;
use zennit\ABAC\Tests\TestHelpers\PolicyBuilder;

class LoadTest extends TestCase
{
    use PolicyBuilder;

    private AbacService $abacService;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->abacService = $this->app->make(AbacService::class);
    }

	/**
	 * @throws UnsupportedOperatorException
	 * @throws InvalidArgumentException
	 * @throws ValidationException
	 */
    public function test_handles_large_number_of_policies(): void
    {
        // Create 100 policies
        for ($i = 0; $i < 100; $i++) {
            $this->createPolicy("resource{$i}", PermissionOperations::SHOW->value, [
                [
                    'operator' => PolicyOperators::EQUALS,
                    'attributes' => [['attribute_name' => 'role', 'attribute_value' => 'user']],
                ],
            ]);
        }

        $startTime = microtime(true);

        $subject = new stdClass();
        $subject->id = 1;

        $context = new AccessContext(
            subject: $subject,
            resource: 'resource50', // Middle of the range
            operation: PermissionOperations::SHOW->value
        );

        $this->abacService->evaluate($context);

        $executionTime = microtime(true) - $startTime;

        // Assert execution time is under 100ms
        $this->assertLessThan(0.1, $executionTime);
    }

	/**
	 * @throws InvalidArgumentException
	 * @throws UnsupportedOperatorException
	 * @throws ValidationException
	 */
    public function test_handles_complex_nested_conditions(): void
    {
        $policy = $this->createPolicy('posts', PermissionOperations::UPDATE->value, [
            [
                'operator' => PolicyOperators::AND,
                'attributes' => [
                    ['attribute_name' => 'role', 'attribute_value' => 'admin'],
                    ['attribute_name' => 'department', 'attribute_value' => 'IT'],
                ],
            ],
            [
                'operator' => PolicyOperators::OR,
                'attributes' => [
                    ['attribute_name' => 'is_owner', 'attribute_value' => 'true'],
                    ['attribute_name' => 'is_moderator', 'attribute_value' => 'true'],
                ],
            ],
        ]);

        $startTime = microtime(true);

        $subject = new stdClass();
        $subject->id = 1;

        $context = new AccessContext(
            subject: $subject,
            resource: 'posts',
            operation: PermissionOperations::UPDATE->value
        );

        $this->abacService->evaluate($context);

        $executionTime = microtime(true) - $startTime;

        // Assert execution time is under 50ms
        $this->assertLessThan(0.05, $executionTime);
    }
}
