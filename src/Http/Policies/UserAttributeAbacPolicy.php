<?php

namespace zennit\ABAC\Http\Policies;

use zennit\ABAC\Http\Policies\Core\AbacPolicy;
use zennit\ABAC\Models\UserAttribute;

class UserAttributeAbacPolicy extends AbacPolicy
{
    protected static function getResourceClass(): string
    {
        return UserAttribute::class;
    }
}
