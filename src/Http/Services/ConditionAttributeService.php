<?php

namespace zennit\ABAC\Http\Services;

use Throwable;
use zennit\ABAC\Models\ConditionAttribute;

readonly class ConditionAttributeService
{
    public function index(int $condition): array
    {
        return ConditionAttribute::where('collection_condition_id', $condition)->get();
    }

    public function store(array $data, int $condition): array
    {
        return ConditionAttribute::create([...$data, 'condition_attribute_id' => $condition])->toArray();
    }

    public function show(int $condition): ConditionAttribute
    {
        return ConditionAttribute::findOrFail($condition);
    }

    /**
     * @throws Throwable
     */
    public function update(array $data, int $condition): ConditionAttribute
    {
        $condition = ConditionAttribute::findOrFail($condition);
        $condition->updateOrFail($data);

        return $condition;
    }

    /**
     * @throws Throwable
     */
    public function destroy(int $condition): void
    {
        ConditionAttribute::findOrFail($condition)->deleteOrFail();
    }
}
