<?php

namespace zennit\ABAC\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    public function rules(): array
    {
        return $this->handleUpdate($this->getRules());
    }

    protected function handleUpdate(array $rules): array
    {
        if (!$this->isMethod('PATCH')) {
            return $rules;
        }

        return array_map(function ($fieldRules) {
            return array_map(
                fn ($rule) => $rule === 'required' ? 'sometimes' : $rule,
                (array) $fieldRules
            );
        }, $rules);
    }

    abstract protected function getRules(): array;

    public function authorize(): bool
    {
        return true;
    }
}
