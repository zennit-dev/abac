<?php

namespace zennit\ABAC\Http\Policies;

use zennit\ABAC\Http\Policies\Core\AbacPolicy;

class PolicyAbacPolicy extends AbacPolicy
{
    protected static function getResourceClass(): string
    {
        return AbacPolicy::class;
    }
}
