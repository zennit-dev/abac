<?php

namespace zennit\ABAC\Strategies\Contracts;

interface ListOperatorInterface extends OperatorInterface
{
    public function evaluate(mixed $values, mixed $against): bool;
}
