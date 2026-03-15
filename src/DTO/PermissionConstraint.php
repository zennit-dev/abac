<?php

namespace zennit\ABAC\DTO;

class PermissionConstraint
{
    public function __construct(
        public string $key,
        public string $operator,
        public string $value,
    ) {}

    /**
     * @return array{key: string, operator: string, value: string}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'operator' => $this->operator,
            'value' => $this->value,
        ];
    }
}
