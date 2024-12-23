<?php

namespace zennit\ABAC\Repositories;

use Illuminate\Database\Eloquent\Collection;
use zennit\ABAC\Models\Permission;
use zennit\ABAC\Models\Policy;

class PolicyRepository
{
    public function getPoliciesFor(string $resource, string $operation = 'all'): Collection
    {
        if ($operation === 'all') {
            return Policy::query()
                ->whereHas('permission', function ($query) use ($resource) {
                    $query->where('resource', $resource);
                })
                ->with(['conditions.attributes'])
                ->get();
        }

        return Policy::query()
            ->whereHas('permission', function ($query) use ($resource, $operation) {
                $query->where([
                    'resource' => $resource,
                    'operation' => $operation,
                ]);
            })
            ->with(['conditions.attributes'])
            ->get();
    }

    public function create(array $data): Policy
    {
        $permission = Permission::firstOrCreate([
            'resource' => $data['resource'],
            'operation' => $data['operation'],
        ]);

        return Policy::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'permission_id' => $permission->id,
        ]);
    }

    public function delete(int $id): bool
    {
        return Policy::destroy($id) > 0;
    }

    public function all(): Collection
    {
        return Policy::with(['permission', 'conditions.attributes'])->get();
    }
}
