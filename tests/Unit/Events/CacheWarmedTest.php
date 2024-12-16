<?php

namespace zennit\ABAC\Tests\Unit\Events;

use PHPUnit\Framework\TestCase;
use zennit\ABAC\Events\CacheWarmed;

class CacheWarmedTest extends TestCase
{
    public function testEventConstruction(): void
    {
        $event = new CacheWarmed(1, 0.5, ['test' => 'data']);
        $this->assertInstanceOf(CacheWarmed::class, $event);
    }
}
