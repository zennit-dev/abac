<?php

namespace zennit\ABAC\Strategies\Operators;

use zennit\ABAC\Strategies\OperatorInterface;

class NotInOperator implements OperatorInterface
{
    public function evaluate(mixed $value1, mixed $value2): bool
    {
        if (!is_array($value2)) {
            return true;
        }

        return !in_array($value1, $value2);
    }
}
