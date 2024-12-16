<?php

namespace zennit\ABAC\Tests\Unit\Events;

use PHPUnit\Framework\TestCase;
use stdClass;
use zennit\ABAC\Events\AccessEvaluated;

class AccessEvaluatedTest extends TestCase
{
    public function testEventConstruction(): void
    {
        $subject = new stdClass();
        $event = new AccessEvaluated($subject, 'resource', 'operation', [], true);
        $this->assertInstanceOf(AccessEvaluated::class, $event);
    }
}
