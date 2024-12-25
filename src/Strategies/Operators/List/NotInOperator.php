<?php

namespace zennit\ABAC\Strategies\Operators\List;

use zennit\ABAC\Strategies\Contracts\ListOperatorInterface;

class NotInOperator implements ListOperatorInterface
{
    public function evaluate(mixed $values, mixed $against): bool
    {
        if (!is_array($against)) {
            return true;
        }

        return !in_array($values, $against);
    }
}
