<?php

namespace zennit\ABAC\Tests\Unit\Jobs;

use PHPUnit\Framework\TestCase;
use stdClass;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\PolicyEvaluationResult;
use zennit\ABAC\Jobs\EvaluateAccessJob;
use zennit\ABAC\Services\AbacService;

class EvaluateAccessJobTest extends TestCase
{
    public function testJobHandle(): void
    {
        $subject = new stdClass();
        $context = new AccessContext($subject, 'resource', 'operation');

        $abacService = $this->createMock(AbacService::class);
        $abacService->expects($this->once())
            ->method('evaluate')
            ->willReturn(new PolicyEvaluationResult(true, 'Granted'));

        $job = new EvaluateAccessJob($context);
        $result = $job->handle($abacService);

        $this->assertInstanceOf(PolicyEvaluationResult::class, $result);
    }
}
