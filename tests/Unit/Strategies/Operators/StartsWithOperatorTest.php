<?php

namespace zennit\ABAC\Tests\Unit\Strategies\Operators;

use zennit\ABAC\Tests\TestCase;
use zennit\ABAC\Strategies\Operators\StartsWithOperator;

class StartsWithOperatorTest extends TestCase
{
    public function testEvaluate()
    {
        $operator = new StartsWithOperator();
        $result = $operator->evaluate('test', 'test');
        $this->assertIsBool($result);
    }
}
