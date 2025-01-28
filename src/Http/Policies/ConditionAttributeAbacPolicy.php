<?php

namespace zennit\ABAC\Http\Policies;

use zennit\ABAC\Http\Policies\Core\AbacPolicy;
use zennit\ABAC\Models\ConditionAttribute;

class ConditionAttributeAbacPolicy extends AbacPolicy
{
    protected static function getResourceClass(): string
    {
        return ConditionAttribute::class;
    }
}
