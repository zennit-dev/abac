<?php

namespace zennit\ABAC\Tests\Unit\Enums;

use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Tests\TestCase;

class PermissionOperationsTest extends TestCase
{
    public function testIsValid()
    {
        $this->assertTrue(PermissionOperations::isValid('index'));
    }

    public function testValues()
    {
        $this->assertNotEmpty(PermissionOperations::values());
    }
}
