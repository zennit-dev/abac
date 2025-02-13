<?php

namespace zennit\ABAC\Http\Requests;

use Illuminate\Validation\Rule;
use zennit\ABAC\Enums\PolicyMethod;

class AbacPolicyRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'method' => ['required', Rule::enum(PolicyMethod::class)],
            'resource' => [
                'required',
                'string',
                'regex:/^App\\\\Models(\\\\[A-Z][A-Za-z0-9_]*)+$/',
            ],
            'chains' => [
                'array',
                'max:1',
                function ($attribute, $value, $fail) {
                    if (is_array($value) && count($value) > 0) {
                        foreach ($value as $chain) {
                            $this->validateChainStructure($chain, $fail);
                        }
                    }
                },
            ],
        ];
    }

    protected function validateChainStructure($chain, $fail): void
    {
        $childCount = 0;

        // Count chains
        if (isset($chain['chains']) && is_array($chain['chains'])) {
            $childCount += count($chain['chains']);
        }

        // Count checks
        if (isset($chain['checks']) && is_array($chain['checks'])) {
            $childCount += count($chain['checks']);
        }

        if ($childCount > 2) {
            $fail('Each chain can have a maximum of 2 children (chains or checks combined)');
        }
    }
}
