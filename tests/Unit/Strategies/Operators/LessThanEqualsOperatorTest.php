<?php

namespace zennit\ABAC\Tests\Unit\Strategies\Operators;

use zennit\ABAC\Tests\TestCase;
use zennit\ABAC\Strategies\Operators\LessThanEqualsOperator;

class LessThanEqualsOperatorTest extends TestCase
{
    public function testEvaluate()
    {
        $operator = new LessThanEqualsOperator();
        $result = $operator->evaluate('test', 'test');
        $this->assertIsBool($result);
    }
}
