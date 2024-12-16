<?php

namespace zennit\ABAC\Tests\Unit\Facades;

use Illuminate\Contracts\Container\BindingResolutionException;
use Orchestra\Testbench\TestCase;
use stdClass;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Facades\Abac;
use zennit\ABAC\Providers\AbacServiceProvider;

class AbacTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure database is in a clean state
        $this->artisan('migrate:fresh');
    }

    protected function getPackageProviders($app): array
    {
        return [
            AbacServiceProvider::class,
        ];
    }

    public function test_facade_accessor(): void
    {
        $this->assertEquals('abac', Abac::getFacadeAccessor());
    }

    /**
     * @throws BindingResolutionException
     */
    public function test_evaluate_method(): void
    {
        $subject = new stdClass();
        $subject->id = 1;
        $context = new AccessContext($subject, 'resource', 'operation');
        $result = Abac::evaluate($context);
        $this->assertNotNull($result);
    }

    /**
     * @throws BindingResolutionException
     */
    public function test_can_method(): void
    {
        $subject = new stdClass();
        $subject->id = 1;
        $context = new AccessContext($subject, 'resource', 'operation');
        $result = Abac::can($context);
        $this->assertIsBool($result);
    }
}
