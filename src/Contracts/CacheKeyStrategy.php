<?php

namespace zennit\ABAC\Contracts;

use zennit\ABAC\DTO\AccessContext;

interface CacheKeyStrategy
{
    public function make(AccessContext $context, bool $includeContext): string;
}
