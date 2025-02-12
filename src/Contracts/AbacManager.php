<?php

namespace zennit\ABAC\Contracts;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AccessResult;

interface AbacManager
{
    public function can(AccessContext $context): bool;

    public function evaluate(AccessContext $context): AccessResult;
}
