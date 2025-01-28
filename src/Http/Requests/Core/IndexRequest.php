<?php

namespace zennit\ABAC\Http\Requests\Core;

class IndexRequest extends Request
{
    public function getRules(): array
    {
        return [
            'perPage' => ['sometimes', 'integer', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'perPage' => $this->query('perPage', 20),
            'page' => $this->query('page', 1),
        ]);
    }
}
