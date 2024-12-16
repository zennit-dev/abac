<?php

namespace zennit\ABAC\Repositories;

use Illuminate\Database\Eloquent\Collection;
use zennit\ABAC\Contracts\PolicyRepositoryInterface;
use zennit\ABAC\Models\Permission;
use zennit\ABAC\Models\Policy;

class PolicyRepository implements PolicyRepositoryInterface
{
    public function getPoliciesFor(string $resource, string $operation): Collection
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

    public function findById(int $id): ?Policy
    {
        return Policy::with(['conditions.attributes'])->find($id);
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

    public function createCondition(Policy $policy, string $operator, array $attributes): void
    {
        $condition = $policy->conditions()->create([
            'operator' => $operator,
        ]);

        foreach ($attributes as $attribute) {
            $condition->attributes()->create([
                'attribute_name' => $attribute['name'],
                'attribute_value' => $attribute['value'],
            ]);
        }
    }

    public function delete(int $id): bool
    {
        return Policy::destroy($id) > 0;
    }

    public function getAllPolicies(): Collection
    {
        return Policy::with(['permission', 'conditions.attributes'])->get();
    }

    public function getPoliciesByResource(string $resource): Collection
    {
        return Policy::query()
            ->whereHas('permission', function ($query) use ($resource) {
                $query->where('resource', $resource);
            })
            ->with(['conditions.attributes'])
            ->get();
    }

    public function updatePolicy(int $id, array $data): bool
    {
        $policy = Policy::find($id);
        if (!$policy) {
            return false;
        }

        if (isset($data['resource']) || isset($data['operation'])) {
            $permission = Permission::firstOrCreate([
                'resource' => $data['resource'] ?? $policy->permission->resource,
                'operation' => $data['operation'] ?? $policy->permission->operation,
            ]);
            $data['permission_id'] = $permission->id;
        }

        return $policy->update(array_intersect_key($data, array_flip([
            'name', 'description', 'permission_id',
        ])));
    }

    public function all(): Collection
    {
        return Policy::with(['permission', 'conditions.attributes'])->get();
    }
}
