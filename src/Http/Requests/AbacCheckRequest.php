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
            'chains.*.checks' => [
                'required',
                'array',
                'max:2',
                function ($attribute, $value, $fail) {
                    $chainIndex = explode('.', $attribute)[1];
                    $chain = $this->input("chains.$chainIndex");

                    $totalChildren = count($value);
                    if (isset($chain['chains'])) {
                        $totalChildren += count($chain['chains']);
                    }

                    if ($totalChildren > 2) {
                        $fail('Total number of children (chains + checks) cannot exceed 2');
                    }
                },
            ],
            'chains.*.checks.*.chain_id' => ['required', 'integer', 'exists:' . AbacChain::class . ',id'],
            'chains.*.checks.*.operator' => ['required', 'string', Rule::in(AllOperators::values(LogicalOperators::cases()))],
            'chains.*.checks.*.key' => ['required', 'string'],
            'chains.*.checks.*.value' => ['required', 'string'],
        ];
    }
}
