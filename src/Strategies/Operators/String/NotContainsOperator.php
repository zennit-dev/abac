<?php

namespace zennit\ABAC\Strategies\Operators\String;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Strategies\Contracts\StringOperatorInterface;
use zennit\ABAC\Strategies\Traits\HandlesContextValues;

class NotContainsOperator implements StringOperatorInterface
{
    use HandlesContextValues;

    public function evaluate(mixed $values, mixed $against, AccessContext $context): bool
    {
        $values = $this->resolveIfContextValue($values, $context);
        $against = $this->resolveIfContextValue($against, $context);

        if (is_array($values)) {
            return !in_array($against, $values);
        }

        if (is_string($values) && is_string($against)) {
            return !str_contains($values, $against);
        }

        return true;
    }
}
