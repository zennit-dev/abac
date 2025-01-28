<?php

namespace zennit\ABAC\Http\Requests;

use Illuminate\Validation\Rule;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Http\Requests\Core\Request;
use zennit\ABAC\Models\PolicyCollection;

class CollectionConditionRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'operator' => ['required', 'string', Rule::enum(LogicalOperators::class)],
            'policy_collection_id' => ['required', 'integer', 'exists:' . PolicyCollection::class . ',id'],
            'condition_attributes' => [
                'sometimes',
                'array',
                Rule::requiredIf(function () {
                    return $this->boolean($this->query('chain', false));
                }),
            ],
        ];
    }
}
