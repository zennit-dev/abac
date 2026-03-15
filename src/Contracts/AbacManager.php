<?php

namespace zennit\ABAC\Contracts;

use Illuminate\Support\Collection;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AccessResult;
use zennit\ABAC\DTO\PermissionGrant;

interface AbacManager
{
    public function can(AccessContext $context): bool;

    public function evaluate(AccessContext $context): AccessResult;

    /**
     * @param  array<string, mixed>|array<int, array<string, mixed>>|string  $constraints
     */
    public function addPermission(string $method, string $resource, array|string $constraints): PermissionGrant;

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, PermissionGrant>
     */
    public function getPermissions(?string $method = null, ?string $resource = null, array $filters = []): Collection;

    public function getPermission(int $grantId): ?PermissionGrant;

    /**
     * @param  array<string, mixed>|array<int, array<string, mixed>>|string  $constraints
     */
    public function updatePermission(int $grantId, array|string $constraints): PermissionGrant;

    public function removePermission(int $grantId): bool;

    /**
     * @param  array<string, mixed>|array<int, array<string, mixed>>|string|null  $constraints
     */
    public function removePermissions(string $method, string $resource, array|string|null $constraints = null): int;
}
