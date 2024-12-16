<?php

namespace zennit\ABAC\Tests\Unit\Jobs;

use Illuminate\Contracts\Events\Dispatcher;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use stdClass;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\PolicyEvaluationResult;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Jobs\BatchEvaluateAccessJob;
use zennit\ABAC\Services\AbacService;

class BatchEvaluateAccessJobTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ValidationException
     * @throws InvalidArgumentException
     * @throws UnsupportedOperatorException
     */
    public function testJobHandle(): void
    {
        $subject = new stdClass();
        $context = new AccessContext($subject, 'resource', 'operation');
        $contexts = [$context];

        $evaluationResult = new PolicyEvaluationResult(
            granted: true,
            reason: 'Access granted',
            context: [],
            matchedPolicies: []
        );

        $abacService = $this->createMock(AbacService::class);
        $abacService->expects($this->once())
            ->method('evaluate')
            ->willReturn($evaluationResult);

        $eventDispatcher = $this->createMock(Dispatcher::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with('TestEvent', $this->isType('array'));

        $job = new BatchEvaluateAccessJob($contexts, 'TestEvent');
        $job->handle($abacService, $eventDispatcher);
    }
}
