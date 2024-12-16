<?php

namespace zennit\ABAC\Tests\Unit\Strategies\Operators;

use zennit\ABAC\Strategies\Operators\GreaterThanEqualsOperator;
use zennit\ABAC\Tests\TestCase;

class GreaterThanEqualsOperatorTest extends TestCase
{
    public function testEvaluate()
    {
        $operator = new GreaterThanEqualsOperator();
        $result = $operator->evaluate('test', 'test');
        $this->assertIsBool($result);
    }
}
