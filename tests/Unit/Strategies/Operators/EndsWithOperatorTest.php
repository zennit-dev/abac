<?php

namespace zennit\ABAC\Tests\Unit\Strategies\Operators;

use zennit\ABAC\Strategies\Operators\EndsWithOperator;
use zennit\ABAC\Tests\TestCase;

class EndsWithOperatorTest extends TestCase
{
    public function testEvaluate()
    {
        $operator = new EndsWithOperator();
        $result = $operator->evaluate('test', 'test');
        $this->assertIsBool($result);
    }
}
