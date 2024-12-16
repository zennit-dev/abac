<?php

namespace zennit\ABAC\Strategies\Operators;

use zennit\ABAC\Strategies\OperatorInterface;

class ContainsOperator implements OperatorInterface
{
    public function evaluate(mixed $value1, mixed $value2): bool
    {
        if (is_array($value1)) {
            return in_array($value2, $value1);
        }

        if (is_string($value1) && is_string($value2)) {
            return str_contains($value1, $value2);
        }

        return false;
    }
}
