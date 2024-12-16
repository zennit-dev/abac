<?php

namespace zennit\ABAC\Strategies\Operators;

use zennit\ABAC\Strategies\OperatorInterface;

class LessThanEqualsOperator implements OperatorInterface
{
    public function evaluate(mixed $value1, mixed $value2): bool
    {
        return $value1 <= $value2;
    }
}
