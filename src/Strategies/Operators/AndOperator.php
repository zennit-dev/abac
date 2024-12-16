<?php

namespace zennit\ABAC\Strategies\Operators;

use zennit\ABAC\Strategies\OperatorInterface;

class AndOperator implements OperatorInterface
{
    public function evaluate($value1, $value2): bool
    {
        return $value1 && $value2;
    }
}
