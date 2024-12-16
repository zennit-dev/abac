<?php

namespace zennit\ABAC\Strategies\Operators;

use zennit\ABAC\Strategies\OperatorInterface;

class NotOperator implements OperatorInterface
{
    public function evaluate(mixed $value1, mixed $value2): bool
    {
        return !$value1;
    }
}
