<?php

namespace zennit\ABAC\Strategies\Contracts;

interface ArithmeticOperatorInterface extends OperatorInterface
{
    public function evaluate(mixed $values, mixed $against): bool;
}
