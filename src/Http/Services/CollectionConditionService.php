<?php

namespace zennit\ABAC\Http\Services;

use Throwable;
use zennit\ABAC\Models\CollectionCondition;

readonly class CollectionConditionService
{
    public function __construct(protected ConditionAttributeService $service)
    {
    }

    public function index(int $collection): array
    {
        return CollectionCondition::where('policy_collection_id', $collection)->toArray();
    }

    public function store(array $data, int $collection, bool $chain = false): array
    {
        $condition = CollectionCondition::create([...$data, 'policy_collection_id' => $collection]);
        $response = $condition->toArray();

        if ($chain) {
            $attributes = array_map(fn ($attribute) => $this->service->store($attribute, $condition->id), $data['attributes']);
            $response['attributes'] = $attributes;
        }

        return $response;
    }

    public function show(int $condition): CollectionCondition
    {
        return CollectionCondition::findOrFail($condition);
    }

    /**
     * @throws Throwable
     */
    public function update(array $data, int $condition): CollectionCondition
    {
        $condition = CollectionCondition::findOrFail($condition);
        $condition->updateOrFail($data);

        return $condition;
    }

    /**
     * @throws Throwable
     */
    public function destroy(int $condition): void
    {
        CollectionCondition::findOrFail($condition)->deleteOrFail();
    }
}
