<?php

namespace zennit\ABAC\Strategies\Operators\Logical;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Strategies\Contracts\LogicalOperatorInterface;
use zennit\ABAC\Strategies\Traits\HandlesContextValues;

class AndOperator implements LogicalOperatorInterface
{
    use HandlesContextValues;

    public function evaluate(mixed $values, mixed $against = null, ?AccessContext $context = null): bool
    {
        if (!$context) {
            return false;
        }

        if (is_array($values)) {
            $resolvedValues = array_map(
                fn ($value) => $this->resolveIfContextValue($value, $context),
                $values
            );

            return !in_array(false, $resolvedValues, true);
        }

        $values = $this->resolveIfContextValue($values, $context);
        $against = $this->resolveIfContextValue($against, $context);

        return $values && $against;
    }
}
