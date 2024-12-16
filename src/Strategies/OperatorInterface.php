<?php

namespace zennit\ABAC\Strategies;

interface OperatorInterface
{
    public function evaluate(mixed $value1, mixed $value2): bool;
}
