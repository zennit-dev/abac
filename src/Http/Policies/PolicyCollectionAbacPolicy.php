<?php

namespace zennit\ABAC\Http\Policies;

use zennit\ABAC\Http\Policies\Core\AbacPolicy;
use zennit\ABAC\Models\PolicyCollection;

class PolicyCollectionAbacPolicy extends AbacPolicy
{
    protected static function getResourceClass(): string
    {
        return PolicyCollection::class;
    }
}
