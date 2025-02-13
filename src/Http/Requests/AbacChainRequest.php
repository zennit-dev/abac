<?php

namespace zennit\ABAC\Http\Requests;

use Illuminate\Validation\Rule;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Models\AbacChain;

class AbacChainRequest extends Request
{
    protected function getRules(): array
    {
        $baseRules = [
            'operator' => ['required', 'string', Rule::enum(LogicalOperators::class)],
        ];

        return $this->route('policy')
            ? $this->getSingleChainRules($baseRules)
            : $this->getBulkChainRules();
    }

    protected function getSingleChainRules(array $baseRules): array
    {
        return array_merge($baseRules, [
            'policy_id' => ['prohibited'],
            'chain_id' => ['integer', 'exists:' . AbacChain::class . ',id'],
            'chains' => [
                'array',
                'max:2',
                function ($attribute, $value, $fail) {
                    $this->validateChildrenCount($value, $fail);
                },
            ],
            'chains.*' => $this->getRecursiveChainRules(),
        ]);
    }

    protected function validateChildrenCount($chain, $fail): void
    {
        $childCount = 0;

        if (isset($chain['chains'])) {
            $childCount += count($chain['chains']);
        }
        if (isset($chain['checks'])) {
            $childCount += count($chain['checks']);
        }

        if ($childCount > 2) {
            $fail('Each chain can have a maximum of 2 children (chains or checks combined)');
        }
    }

    protected function getRecursiveChainRules(): array
    {
        return [
            'array',
            function ($attribute, $value, $fail) {
                if (!isset($value['policy_id']) && !isset($value['chain_id']) && !isset($value['chains'])) {
                    $fail('Each chain must have either policy_id, chain_id, or nested chains');
                }

                if (isset($value['policy_id']) && isset($value['chain_id'])) {
                    $fail('You cannot set both chain_id and policy_id at the same time');
                }
            },
        ];
    }

    protected function getBulkChainRules(): array
    {
        return [
            'chains' => [
                'array',
                'required',
                function ($attribute, $value, $fail) {
                    foreach ($value as $chain) {
                        $this->validateChildrenCount($chain, $fail);
                    }
                },
            ],
            'chains.*' => $this->getRecursiveChainRules(),
        ];
    }
}
