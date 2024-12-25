<?php

namespace zennit\ABAC\Strategies\Operators\Logical;

use Illuminate\Support\Collection;
use zennit\ABAC\Strategies\Contracts\LogicalOperatorInterface;

class NotOperator implements LogicalOperatorInterface
{
    public function evaluate(mixed $values, mixed $against = []): bool
    {
        if ($values instanceof Collection) {
            return !$values->every(fn ($value) => $value === true);
        }

        if (is_array($values)) {
            return in_array(false, $values, true) || empty($values);
        }

        return !$values;
    }
}
