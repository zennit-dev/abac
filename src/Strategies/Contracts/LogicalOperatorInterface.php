<?php

namespace zennit\ABAC\Strategies\Contracts;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Strategies\Contracts\Core\OperatorInterface;

interface LogicalOperatorInterface extends OperatorInterface
{
    /**
     * Evaluates a collection of boolean values
     */
    public function evaluate(mixed $values, mixed $against, AccessContext $context): bool;
}
