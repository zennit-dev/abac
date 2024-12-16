<?php

namespace zennit\ABAC\Tests\Unit\Events;

use PHPUnit\Framework\TestCase;
use zennit\ABAC\Events\PolicyDeleted;

class PolicyDeletedTest extends TestCase
{
    public function testEventConstruction(): void
    {
        $event = new PolicyDeleted(1, []);
        $this->assertInstanceOf(PolicyDeleted::class, $event);
    }
}
