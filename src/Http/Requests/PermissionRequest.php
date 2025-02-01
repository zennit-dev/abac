<?php

namespace zennit\ABAC\Http\Requests;

use Illuminate\Validation\Rule;
use zennit\ABAC\Enums\RequestMethods;
use zennit\ABAC\Http\Requests\Core\Request;

class PermissionRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'resource' => ['required', 'string'],
            'operation' => ['required', 'string', Rule::enum(RequestMethods::class)],
            'policies' => [
                'sometimes',
                'array',
                Rule::requiredIf(function () {
                    return $this->boolean($this->query('chain', false));
                }),
            ],
        ];
    }
}
