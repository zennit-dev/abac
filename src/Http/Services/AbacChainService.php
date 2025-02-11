<?php

namespace zennit\ABAC\Http\Services;

use Throwable;
use zennit\ABAC\Models\AbacChain;

readonly class AbacChainService
{
    public function __construct(protected AbacCheckService $service)
    {
    }

    public function index(int $collection): array
    {
        return AbacChain::where('policy_collection_id', $collection)->toArray();
    }

    public function store(array $data, int $collection, bool $chain = false): array
    {
        $condition = AbacChain::create([...$data, 'policy_collection_id' => $collection]);
        $response = $condition->toArray();

        if ($chain) {
            $attributes = array_map(fn ($attribute) => $this->service->store($attribute, $condition->id), $data['attributes']);
            $response['attributes'] = $attributes;
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
