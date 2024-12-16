<?php

namespace zennit\ABAC\Tests\Unit\Strategies\Operators;

use zennit\ABAC\Tests\TestCase;
use zennit\ABAC\Strategies\Operators\NotContainsOperator;

class NotContainsOperatorTest extends TestCase
{
    public function testEvaluate()
    {
        $operator = new NotContainsOperator();
        $result = $operator->evaluate('test', 'test');
        $this->assertIsBool($result);
    }
}
