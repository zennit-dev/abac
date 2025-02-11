<?php

namespace zennit\ABAC\Http\Requests;

use Illuminate\Validation\Rule;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacPolicy;

class AbacChainRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'operator' => ['required', 'string', Rule::enum(LogicalOperators::class)],
            'policy_id' => ['integer', 'exists:' . AbacPolicy::class . ',id', function ($attribute, $value, $fail) {
                if ($this->filled('chain_id') && $value) {
                    $fail('You can not set both chain_id and policy_id at the same time');
                }

                if (!$this->filled('chain_id') && !$value) {
                    $fail('You must set either chain_id or policy_id');
                }
            }],
            'chain_id' => ['integer', 'exists:' . AbacChain::class . ',id', function ($attribute, $value, $fail) {
                if ($this->filled('policy_id') && $value) {
                    $fail('You can not set both chain_id and policy_id at the same time');
                }

                if (!$this->filled('policy_id') && !$value) {
                    $fail('You must set either chain_id or policy_id');
                }
            }],
            'checks' => ['array'],
        ];
    }
}
