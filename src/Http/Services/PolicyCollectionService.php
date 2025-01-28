<?php

namespace zennit\ABAC\Http\Services;

use Throwable;
use zennit\ABAC\Models\PolicyCollection;

readonly class PolicyCollectionService
{
    public function __construct(protected CollectionConditionService $service)
    {
    }

    public function index(int $policy): array
    {
        return PolicyCollection::where('policy_id', $policy)->get();
    }

    public function store(array $data, int $policy, bool $chain = false): array
    {
        $collection = PolicyCollection::create([...$data, 'policy_id' => $policy]);
        $response = $collection->toArray();

        if ($chain) {
            $conditions = array_map(fn ($condition) => $this->service->store($condition, $collection->id, true), $data['conditions']);
            $response['conditions'] = $conditions;
        }

        return $response;
    }

    public function show(int $collection): PolicyCollection
    {
        return PolicyCollection::findOrFail($collection);
    }

    /**
     * @throws Throwable
     */
    public function update(array $data, int $collection): PolicyCollection
    {
        $collection = PolicyCollection::findOrFail($collection);
        $collection->updateOrFail($data);

        return $collection;
    }

    /**
     * @throws Throwable
     */
    public function destroy(int $collection): void
    {
        PolicyCollection::findOrFail($collection)->deleteOrFail();
    }
}
