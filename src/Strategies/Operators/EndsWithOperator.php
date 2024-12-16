<?php

namespace zennit\ABAC\Strategies\Operators;

use zennit\ABAC\Strategies\OperatorInterface;

class EndsWithOperator implements OperatorInterface
{
    public function evaluate(mixed $value1, mixed $value2): bool
    {
        if (!is_string($value1) || !is_string($value2)) {
            return false;
        }

        return str_ends_with($value1, $value2);
    }
}
