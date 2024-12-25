<?php

namespace zennit\ABAC\Strategies\Operators\Logical;

use Illuminate\Support\Collection;
use zennit\ABAC\Strategies\Contracts\LogicalOperatorInterface;

class AndOperator implements LogicalOperatorInterface
{
    public function evaluate($values, $against = null): bool
    {
        if ($values instanceof Collection) {
            return $values->every(fn ($value) => $value === true);
        }

        if (is_array($values)) {
            return !in_array(false, $values, true);
        }

        return $values && $against;
    }
}
