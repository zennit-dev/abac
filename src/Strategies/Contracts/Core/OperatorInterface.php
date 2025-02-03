<?php

namespace zennit\ABAC\Strategies\Contracts\Core;

use zennit\ABAC\DTO\AccessContext;

interface OperatorInterface
{
    public function evaluate(mixed $values, mixed $against, AccessContext $context): bool;
}
