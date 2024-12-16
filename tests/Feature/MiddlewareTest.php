<?php

namespace zennit\ABAC\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use stdClass;
use zennit\ABAC\Contracts\AbacServiceInterface;
use zennit\ABAC\DTO\PolicyEvaluationResult;
use zennit\ABAC\Middleware\EnsurePermissions;
use zennit\ABAC\Tests\TestCase;

class MiddlewareTest extends TestCase
{
    private EnsurePermissions $middleware;

    private AbacServiceInterface $abacMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->abacMock = Mockery::mock(AbacServiceInterface::class);
        $this->middleware = new EnsurePermissions($this->abacMock);
    }

    public function test_allows_authorized_requests(): void
    {
        $this->abacMock->shouldReceive('evaluate')
            ->once()
            ->andReturn(new PolicyEvaluationResult(true, 'Access granted'));

        $user = new stdClass();
        $user->id = 1;

        $request = Request::create('/api/v1/posts', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $this->middleware->handle($request, function () {
            return new Response();
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
