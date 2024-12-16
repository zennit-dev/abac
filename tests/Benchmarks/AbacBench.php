<?php

namespace zennit\ABAC\Tests\Benchmarks;

use Orchestra\Testbench\TestCase;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use Psr\SimpleCache\InvalidArgumentException;
use stdClass;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Providers\AbacServiceProvider;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Tests\TestHelpers\PolicyBuilder;

/**
 * @BeforeMethods({"setUp"})
 */
class AbacBench extends TestCase
{
    use PolicyBuilder;

    private AbacService $abacService;

    private stdClass $subject;

    /**
     * @Revs(1000)
     *
     * @Iterations(5)
     *
     * @throws UnsupportedOperatorException
     * @throws ValidationException
     * @throws InvalidArgumentException
     */
    public function benchEvaluateSimplePolicy(): void
    {
        $context = new AccessContext(
            $this->subject,
            'posts',
            PermissionOperations::INDEX->value
        );

        $this->abacService->evaluate($context);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->abacService = app(AbacService::class);

        // Create test subject
        $this->subject = new stdClass();
        $this->subject->id = 1;
    }

    protected function getPackageProviders($app): array
    {
        return [
            AbacServiceProvider::class,
        ];
    }
}
