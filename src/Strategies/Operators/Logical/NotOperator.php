<?php

namespace zennit\ABAC\Strategies\Operators\Logical;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Strategies\Contracts\LogicalOperatorInterface;
use zennit\ABAC\Strategies\Traits\HandlesContextValues;

class NotOperator implements LogicalOperatorInterface
{
    use HandlesContextValues;

    public function evaluate(mixed $values, mixed $against = [], ?AccessContext $context = null): bool
    {
        if (!$context) {
            return false;
        }

        $values = $this->resolveIfContextValue($values, $context);

        return !$values;
    }
}
