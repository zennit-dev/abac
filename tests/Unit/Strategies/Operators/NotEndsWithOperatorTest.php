<?php

namespace zennit\ABAC\Tests\Unit\Strategies\Operators;

use zennit\ABAC\Tests\TestCase;
use zennit\ABAC\Strategies\Operators\NotEndsWithOperator;

class NotEndsWithOperatorTest extends TestCase
{
    public function testEvaluate()
    {
        $operator = new NotEndsWithOperator();
        $result = $operator->evaluate('test', 'test');
        $this->assertIsBool($result);
    }
}
