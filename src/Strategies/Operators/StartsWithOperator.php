<?php

namespace zennit\ABAC\Strategies\Operators;

use zennit\ABAC\Strategies\OperatorInterface;

class StartsWithOperator implements OperatorInterface
{
    public function evaluate(mixed $value1, mixed $value2): bool
    {
        if (!is_string($value1) || !is_string($value2)) {
            return false;
        }

        return str_starts_with($value1, $value2);
    }
}
