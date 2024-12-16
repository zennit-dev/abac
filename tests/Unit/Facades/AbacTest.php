<?php

namespace zennit\ABAC\Tests\Unit\Facades;

use Illuminate\Contracts\Container\BindingResolutionException;
use Orchestra\Testbench\TestCase;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Facades\Abac;
use zennit\ABAC\Providers\AbacServiceProvider;
use zennit\ABAC\Tests\TestHelpers\TestSubject;

class AbacTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    protected function getPackageProviders($app): array
    {
        return [AbacServiceProvider::class];
    }

	/**
	 * @throws BindingResolutionException
	 */
	public function test_evaluate_method(): void
    {
        $subject = new TestSubject(1);
        $context = new AccessContext($subject, 'resource', 'operation');
        $result = Abac::evaluate($context);
        $this->assertNotNull($result);
    }
}
