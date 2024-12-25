<?php

namespace zennit\ABAC\Strategies\Contracts;

interface OperatorInterface
{
    public function evaluate(mixed $values, mixed $against): bool;
} 