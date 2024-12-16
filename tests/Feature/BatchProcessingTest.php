<?php

namespace zennit\ABAC\Tests\Feature;

use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\SimpleCache\InvalidArgumentException;
use stdClass;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Enums\PolicyOperators;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Services\BatchProcessor;
use zennit\ABAC\Tests\TestCase;
use zennit\ABAC\Tests\TestHelpers\PolicyBuilder;

class BatchProcessingTest extends TestCase
{
    use PolicyBuilder;

    private BatchProcessor $batchProcessor;

    /**
     * @throws UnsupportedOperatorException
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    public function test_processes_multiple_contexts(): void
    {
        $this->createPolicy('posts', PermissionOperations::INDEX->value, [
            [
                'operator' => PolicyOperators::EQUALS->value,
                'attributes' => [['attribute_name' => 'role', 'attribute_value' => 'user']],
            ],
        ]);

        $this->createPolicy('posts', PermissionOperations::UPDATE->value, [
            [
                'operator' => PolicyOperators::EQUALS->value,
                'attributes' => [['attribute_name' => 'role', 'attribute_value' => 'admin']],
            ],
        ]);

        // Create a subject
        $subject = new stdClass();
        $subject->id = 1;

        // Create contexts
        $contexts = [
            new AccessContext($subject, 'posts', PermissionOperations::INDEX->value),
            new AccessContext($subject, 'posts', PermissionOperations::UPDATE->value),
        ];

        // Process batch
        $results = $this->batchProcessor->evaluate($contexts);

        $this->assertCount(2, $results);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->batchProcessor = $this->app->make(BatchProcessor::class);
    }
}
