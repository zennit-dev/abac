<?php

namespace zennit\ABAC\Http\Policies;

use zennit\ABAC\Http\Policies\Core\AbacPolicy;
use zennit\ABAC\Models\CollectionCondition;

class CollectionConditionAbacPolicy extends AbacPolicy
{
    protected static function getResourceClass(): string
    {
        return CollectionCondition::class;
    }
}
