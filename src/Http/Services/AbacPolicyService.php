<?php

namespace zennit\ABAC\Http\Services;

use Throwable;
use zennit\ABAC\Models\AbacPolicy;

readonly class AbacPolicyService
{
    public function __construct(protected AbacChainService $service)
    {
    }

    public function index(): array
    {
        return AbacPolicy::with('chains.checks')->get()->toArray();
    }

    public function store(array $data, bool $chain = false): array
    {
        $policy = AbacPolicy::create($data);
        $response = $policy->toArray();

        if ($chain) {
            $chains = array_map(fn ($policy) => $this->service->store($policy, $policy->id, true), $data['chains']);
            $response['chains'] = $chains;
        }

        return $response;
    }

    public function show(int $policy): AbacPolicy
    {
        return AbacPolicy::findOrFail($policy);
    }

    /**
     * @throws Throwable
     */
    public function update(array $data, int $policy): AbacPolicy
    {
        $policy = AbacPolicy::findOrFail($policy);
        $policy->updateOrFail($data);

        return $policy;
    }

    /**
     * @throws Throwable
     */
    public function destroy(int $policy): void
    {
        AbacPolicy::findOrFail($policy)->deleteOrFail();
    }
}
