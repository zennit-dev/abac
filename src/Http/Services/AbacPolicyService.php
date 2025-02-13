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

    public function store(array $policyData): array
    {
        $policy = AbacPolicy::create([
            'method' => $policyData['method'],
            'resource' => $policyData['resource'],
        ]);

        $response = $policy->toArray();

        // If chains exist, create them recursively
        if (!empty($policyData['chains'])) {
            $response['chains'] = array_map(
                fn ($chainData) => $this->service->store($chainData, $policy->id),
                $policyData['chains']
            );
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
