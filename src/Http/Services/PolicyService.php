<?php

namespace zennit\ABAC\Http\Services;

use Throwable;
use zennit\ABAC\Models\Policy;

readonly class PolicyService
{
    public function __construct(protected PolicyCollectionService $service)
    {
    }

    public function index(int $permission): array
    {
        return Policy::where('permission_id', $permission)->get()->toArray();
    }

    public function store(array $data, int $permission, bool $chain = false): array
    {
        $policy = Policy::create([...$data, 'permission_id' => $permission]);
        $response = $policy->toArray();

        if ($chain) {
            $collections = array_map(fn ($collection) => $this->service->store($collection, $policy->id, true), $data['collection']);
            $response['collections'] = $collections;
        }

        return $response;
    }

    public function show(int $policy): Policy
    {
        return Policy::findOrFail($policy);
    }

    /**
     * @throws Throwable
     */
    public function update(array $data, int $policy): Policy
    {
        $policy = Policy::findOrFail($policy);
        $policy->updateOrFail($data);

        return $policy;
    }

    /**
     * @throws Throwable
     */
    public function destroy(int $policy): void
    {
        Policy::findOrFail($policy)->deleteOrFail();
    }
}
