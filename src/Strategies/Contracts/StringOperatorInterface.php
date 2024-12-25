<?php

namespace zennit\ABAC\Strategies\Contracts;

interface StringOperatorInterface extends OperatorInterface
{
    public function evaluate(mixed $values, mixed $against): bool;
}
