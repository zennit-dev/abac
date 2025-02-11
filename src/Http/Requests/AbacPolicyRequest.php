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
            'subject' => [
                'required',
                'string',
                'regex:/^App\\\\Models(\\\\[A-Z][A-Za-z0-9_]*)+$/',
            ],
            'chains' => ['array'],
        ];
    }
}
