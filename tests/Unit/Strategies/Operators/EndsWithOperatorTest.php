<?php

namespace zennit\ABAC\Tests\Unit\Strategies\Operators;

use zennit\ABAC\Tests\TestCase;
use zennit\ABAC\Strategies\Operators\EndsWithOperator;

class EndsWithOperatorTest extends TestCase
{
    public function testEvaluate()
    {
        $operator = new EndsWithOperator();
        $result = $operator->evaluate('test', 'test');
        $this->assertIsBool($result);
    }
}
