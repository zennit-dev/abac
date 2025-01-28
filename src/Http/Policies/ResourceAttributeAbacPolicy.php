<?php

namespace zennit\ABAC\Http\Policies;

use zennit\ABAC\Http\Policies\Core\AbacPolicy;
use zennit\ABAC\Models\ResourceAttribute;

class ResourceAttributeAbacPolicy extends AbacPolicy
{
    protected static function getResourceClass(): string
    {
        return ResourceAttribute::class;
    }
}
