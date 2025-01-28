<?php

namespace zennit\ABAC\Http\Requests;

use Illuminate\Validation\Rule;
use zennit\ABAC\Enums\Operators\AllOperators;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Http\Requests\Core\Request;
use zennit\ABAC\Models\CollectionCondition;

class ConditionAttributeRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'condition_attribute_id' => ['required', 'integer', 'exists:' . CollectionCondition::class . ',id'],
            'operator' => ['required', 'string', Rule::in(AllOperators::values(LogicalOperators::cases()))],
            'attribute_name' => ['required', 'string'],
            'attribute_value' => ['required', 'string'],
        ];
    }
}
