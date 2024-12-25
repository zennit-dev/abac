<?php

namespace zennit\ABAC\Strategies\Operators\String;

use zennit\ABAC\Strategies\Contracts\StringOperatorInterface;

class NotContainsOperator implements StringOperatorInterface
{
    public function evaluate(mixed $values, mixed $against): bool
    {
        if (is_array($values)) {
            return !in_array($against, $values);
        }

        if (is_string($values) && is_string($against)) {
            return !str_contains($values, $against);
        }

        return true;
    }
}
