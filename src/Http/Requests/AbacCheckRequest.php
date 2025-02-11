<?php

namespace zennit\ABAC\Http\Requests;

use Illuminate\Validation\Rule;
use zennit\ABAC\Enums\Operators\AllOperators;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Models\AbacChain;

class AbacCheckRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'chain_id' => ['required', 'integer', 'exists:' . AbacChain::class . ',id'],
            'operator' => ['required', 'string', Rule::in(AllOperators::values(LogicalOperators::cases()))],
            'context_accessor' => ['required', 'string'],
            'value' => ['required', 'string'],
        ];
    }
}
