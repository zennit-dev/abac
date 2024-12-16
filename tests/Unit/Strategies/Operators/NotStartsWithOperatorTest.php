<?php

namespace zennit\ABAC\Tests\Unit\Strategies\Operators;

use zennit\ABAC\Strategies\Operators\NotStartsWithOperator;
use zennit\ABAC\Tests\TestCase;

class NotStartsWithOperatorTest extends TestCase
{
    public function testEvaluate()
    {
        $operator = new NotStartsWithOperator();
        $result = $operator->evaluate('test', 'test');
        $this->assertIsBool($result);
    }
}
