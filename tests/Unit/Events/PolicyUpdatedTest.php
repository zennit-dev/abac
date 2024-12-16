<?php

namespace zennit\ABAC\Tests\Unit\Events;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use zennit\ABAC\Events\PolicyUpdated;
use zennit\ABAC\Models\Policy;

class PolicyUpdatedTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testEventConstruction(): void
    {
        $policy = $this->createMock(Policy::class);
        $event = new PolicyUpdated($policy, ['resource' => 'test']);
        $this->assertInstanceOf(PolicyUpdated::class, $event);
    }
}
