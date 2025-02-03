<?php

namespace zennit\ABAC\Strategies\Operators\Arithmetic;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Strategies\Contracts\ArithmeticOperatorInterface;
use zennit\ABAC\Strategies\Traits\HandlesContextValues;

class EqualsOperator implements ArithmeticOperatorInterface
{
    use HandlesContextValues;

    public function evaluate(mixed $values, mixed $against, AccessContext $context): bool
    {
        $values = $this->resolveIfContextValue($values, $context);
        $against = $this->resolveIfContextValue($against, $context);

        return $values === $against;
    }
}
