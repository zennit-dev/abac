<?php

namespace zennit\ABAC\Tests\Unit\Strategies\Operators;

use zennit\ABAC\Tests\TestCase;
use zennit\ABAC\Strategies\Operators\GreaterThanEqualsOperator;

class GreaterThanEqualsOperatorTest extends TestCase
{
    public function testEvaluate()
    {
        $operator = new GreaterThanEqualsOperator();
        $result = $operator->evaluate('test', 'test');
        $this->assertIsBool($result);
    }
}
