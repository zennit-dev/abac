<?php

namespace zennit\ABAC\Tests\Unit\Strategies\Operators;

use zennit\ABAC\Strategies\Operators\NotInOperator;
use zennit\ABAC\Tests\TestCase;

class NotInOperatorTest extends TestCase
{
    public function testEvaluate()
    {
        $operator = new NotInOperator();
        $result = $operator->evaluate('test', 'test');
        $this->assertIsBool($result);
    }
}
