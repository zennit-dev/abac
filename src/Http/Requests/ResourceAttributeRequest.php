<?php

namespace zennit\ABAC\Http\Requests;

use zennit\ABAC\Http\Requests\Core\Request;

class ResourceAttributeRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'resource' => ['required', 'string'],
            'attribute_name' => ['required', 'string'],
            'attribute_value' => ['required', 'string'],
        ];
    }
}
