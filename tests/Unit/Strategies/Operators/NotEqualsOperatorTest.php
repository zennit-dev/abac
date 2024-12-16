<?php

namespace zennit\ABAC\Tests\Unit\Strategies\Operators;

use zennit\ABAC\Tests\TestCase;
use zennit\ABAC\Strategies\Operators\NotEqualsOperator;

class NotEqualsOperatorTest extends TestCase
{
    public function testEvaluate()
    {
        $operator = new NotEqualsOperator();
        $result = $operator->evaluate('test', 'test');
        $this->assertIsBool($result);
    }
}
