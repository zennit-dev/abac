<?php

namespace zennit\ABAC\Contracts;

use zennit\ABAC\Models\AbacPolicy;

interface PolicyRepository
{
    public function findByMethodAndResource(string $method, string $resource): ?AbacPolicy;
}
