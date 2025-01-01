<?php

namespace zennit\ABAC\Strategies\Contracts;

interface LogicalOperatorInterface extends OperatorInterface
{
    /**
     * Evaluates a collection of boolean values
     */
    public function evaluate(mixed $values, mixed $against = null): bool;
}
