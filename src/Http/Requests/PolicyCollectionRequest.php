<?php

namespace zennit\ABAC\Http\Requests;

use Illuminate\Validation\Rule;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Http\Requests\Core\Request;
use zennit\ABAC\Models\Policy;

class PolicyCollectionRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'operator' => ['required', 'string', Rule::enum(LogicalOperators::class)],
            'policy_id' => ['required', 'integer', 'exists:' . Policy::class . ',id'],
            'collection_conditions' => [
                'sometimes',
                'array',
                Rule::requiredIf(function () {
                    return $this->boolean($this->query('chain', false));
                }),
            ],
        ];
    }
}
