<?php

namespace zennit\ABAC\Tests\Unit\Events;

use PHPUnit\Framework\TestCase;
use zennit\ABAC\Events\PolicyCreated;
use zennit\ABAC\Models\Policy;

class PolicyCreatedTest extends TestCase
{
    public function testEventConstruction(): void
    {
        $policy = $this->createMock(Policy::class);
        $event = new PolicyCreated($policy);
        $this->assertInstanceOf(PolicyCreated::class, $event);
    }
}
