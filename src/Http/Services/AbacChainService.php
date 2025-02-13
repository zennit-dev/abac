<?php

namespace zennit\ABAC\Http\Services;

use Throwable;
use zennit\ABAC\Models\AbacChain;

readonly class AbacChainService
{
    public function __construct(protected AbacCheckService $checkService)
    {
    }

    public function index(int $collection): array
    {
        return AbacChain::where('policy_collection_id', $collection)->toArray();
    }

    public function store(array $chainData, ?int $policyId = null): array
    {
        $chain = AbacChain::create([
            'operator' => $chainData['operator'],
            'policy_id' => $policyId,
        ]);

        $response = $chain->toArray();

        // Handle nested chains recursively
        if (isset($chainData['chains'])) {
            $response['chains'] = array_map(
                fn ($nestedChain) => $this->store($nestedChain, $policyId),
                $chainData['chains']
            );
        }

        // Handle checks
        if (isset($chainData['checks'])) {
            $response['checks'] = array_map(
                fn ($check) => $this->checkService->store($check, $chain->id),
                $chainData['checks']
            );
        }

        return $response;
    }

    public function show(int $condition): AbacChain
    {
        return AbacChain::findOrFail($condition);
    }

    /**
     * @throws Throwable
     */
    public function update(array $data, int $condition): AbacChain
    {
        $condition = AbacChain::findOrFail($condition);
        $condition->updateOrFail($data);

        return $condition;
    }

    /**
     * @throws Throwable
     */
    public function destroy(int $condition): void
    {
        AbacChain::findOrFail($condition)->deleteOrFail();
    }
}
