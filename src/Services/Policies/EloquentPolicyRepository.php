<?php

namespace zennit\ABAC\Services\Policies;

use zennit\ABAC\Contracts\PolicyRepository;
use zennit\ABAC\Models\AbacPolicy;

class EloquentPolicyRepository implements PolicyRepository
{
    public function findByMethodAndResource(string $method, string $resource): ?AbacPolicy
    {
        return AbacPolicy::query()
            ->where('method', $method)
            ->where('resource', $resource)
            ->first();
    }
}
