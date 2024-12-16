<?php

namespace zennit\ABAC\Tests\Benchmarks;

use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use Psr\SimpleCache\InvalidArgumentException;
use stdClass;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Enums\PolicyOperators;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Models\UserAttribute;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Tests\TestHelpers\PolicyBuilder;

/**
 * @BeforeMethods({"setUp"})
 */
class AbacBench
{
    use PolicyBuilder;

    private AbacService $abacService;

    private stdClass $subject;

    public function setUp(): void
    {
        // Setup test environment
        $this->abacService = app(AbacService::class);

        // Create test subject
        $this->subject = new stdClass;
        $this->subject->id = 1;

        UserAttribute::create([
            'subject_type' => get_class($this->subject),
            'subject_id' => $this->subject->id,
            'attribute_name' => 'role',
            'attribute_value' => 'user',
        ]);
    }

    /**
     * @Revs(1000)
     *
     * @Iterations(5)
     *
     * @throws UnsupportedOperatorException
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    public function benchSimplePolicy(): void
    {
        $this->createPolicy('posts', PermissionOperations::INDEX->value, [
            [
                'operator' => PolicyOperators::EQUALS->value,
                'attributes' => [['attribute_name' => 'role', 'attribute_value' => 'user']],
            ],
        ]);

        $context = new AccessContext(
            subject: $this->subject,
            resource: 'posts',
            operation: PermissionOperations::INDEX->value
        );

        $this->abacService->evaluate($context);
    }

    /**
     * @Revs(1000)
     *
     * @Iterations(5)
     *
     * @throws InvalidArgumentException
     * @throws UnsupportedOperatorException
     * @throws ValidationException
     */
    public function benchComplexPolicy(): void
    {
        $this->createPolicy('posts', PermissionOperations::UPDATE->value, [
            [
                'operator' => PolicyOperators::AND->value,
                'attributes' => [
                    ['attribute_name' => 'role', 'attribute_value' => 'user'],
                    ['attribute_name' => 'department', 'attribute_value' => 'IT'],
                ],
            ],
            [
                'operator' => PolicyOperators::OR->value,
                'attributes' => [
                    ['attribute_name' => 'is_owner', 'attribute_value' => 'true'],
                    ['attribute_name' => 'is_moderator', 'attribute_value' => 'true'],
                ],
            ],
        ]);

        $context = new AccessContext(
            subject: $this->subject,
            resource: 'posts',
            operation: PermissionOperations::UPDATE->value
        );

        $this->abacService->evaluate($context);
    }

    /**
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws InvalidArgumentException
     * @throws UnsupportedOperatorException
     * @throws ValidationException
     */
    public function benchMultiplePolicies(): void
    {
        // Create 100 policies
        for ($i = 0; $i < 100; $i++) {
            $this->createPolicy("resource{$i}", PermissionOperations::INDEX->value, [
                [
                    'operator' => PolicyOperators::EQUALS,
                    'attributes' => [['attribute_name' => 'role', 'attribute_value' => 'user']],
                ],
            ]);
        }

        $context = new AccessContext(
            subject: $this->subject,
            resource: 'resource50',
            operation: PermissionOperations::INDEX->value
        );

        $this->abacService->evaluate($context);
    }
}
