<?php

namespace zennit\ABAC\Strategies\Operators\String;

use zennit\ABAC\Strategies\Contracts\StringOperatorInterface;

class NotEndsWithOperator implements StringOperatorInterface
{
    public function evaluate(mixed $values, mixed $against): bool
    {
        if (!is_string($values) || !is_string($against)) {
            return true;
        }

        return !str_ends_with($values, $against);
    }
}
