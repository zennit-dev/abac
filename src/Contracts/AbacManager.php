<?php

namespace zennit\ABAC\Contracts;

use zennit\ABAC\DTO\AccessContext;

interface AbacManager
{
    public function can(AccessContext $context): bool;
}
