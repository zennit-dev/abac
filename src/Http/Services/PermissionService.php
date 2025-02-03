<?php

namespace zennit\ABAC\Http\Services;

use Throwable;
use zennit\ABAC\Models\Permission;

readonly class PermissionService
{
    public function __construct(protected PolicyService $service)
    {
    }

    public function index(): array
    {
        return Permission::all();
    }

    public function store(array $data, bool $chain = false): array
    {
        $permission = Permission::create($data);
        $response = $permission->toArray();

        if ($chain) {
            $policies = array_map(fn ($policy) => $this->service->store($policy, $permission->id, true), $data['policies']);
            $response['policies'] = $policies;
        }

        return $response;
    }

    public function show(int $permission): Permission
    {
        return Permission::findOrFail($permission);
    }

    /**
     * @throws Throwable
     */
    public function update(array $data, int $permission): Permission
    {
        $permission = Permission::findOrFail($permission);
        $permission->updateOrFail($data);

        return $permission;
    }

    /**
     * @throws Throwable
     */
    public function destroy(int $permission): void
    {
        Permission::findOrFail($permission)->deleteOrFail();
    }
}
