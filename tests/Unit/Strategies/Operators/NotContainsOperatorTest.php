<?php

namespace zennit\ABAC\Tests\Unit\Strategies\Operators;

use zennit\ABAC\Strategies\Operators\NotContainsOperator;
use zennit\ABAC\Tests\TestCase;

class NotContainsOperatorTest extends TestCase
{
    public function testEvaluate()
    {
        $operator = new NotContainsOperator();
        $result = $operator->evaluate('test', 'test');
        $this->assertIsBool($result);
    }
}
