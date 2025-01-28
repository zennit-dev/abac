<?php

namespace zennit\ABAC\Http\Requests;

use Illuminate\Validation\Rule;
use zennit\ABAC\Http\Requests\Core\Request;
use zennit\ABAC\Models\Permission;

class PolicyRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'name' => ['required', 'string'],
            'permission_id' => ['required', 'integer', 'exists:' . Permission::class . ',id'],
            'policy_collections' => [
                'sometimes',
                'array',
                Rule::requiredIf(function () {
                    return $this->boolean($this->query('chain', false));
                }),
            ],
        ];
    }
}
