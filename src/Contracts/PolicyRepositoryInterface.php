<?php

namespace zennit\ABAC\Contracts;

use Illuminate\Database\Eloquent\Collection;
use zennit\ABAC\Models\Policy;

interface PolicyRepositoryInterface
{
    public function getPoliciesFor(string $resource, string $operation): Collection;

    public function findById(int $id): ?Policy;

    public function create(array $data): Policy;

    public function createCondition(Policy $policy, string $operator, array $attributes): void;

    public function delete(int $id): bool;

    public function getAllPolicies(): Collection;

    public function getPoliciesByResource(string $resource): Collection;

    public function updatePolicy(int $id, array $data): bool;

    public function all(): Collection;
}
