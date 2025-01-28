<?php

namespace zennit\ABAC\Http\Policies;

use zennit\ABAC\Http\Policies\Core\AbacPolicy;
use zennit\ABAC\Models\Permission;

class PermissionAbacPolicy extends AbacPolicy
{
    protected static function getResourceClass(): string
    {
        return Permission::class;
    }
}
