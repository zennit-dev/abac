<?php

namespace zennit\ABAC\DTO;

use Illuminate\Support\Collection;

class PermissionGrant
{
    /**
     * @param  Collection<int, PermissionConstraint>  $constraints
     */
    public function __construct(
        public int $id,
        public string $method,
        public string $resource,
        public Collection $constraints,
    ) {}

    /**
     * @return array{id: int, method: string, resource: string, constraints: array<int, array{key: string, operator: string, value: string}>}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'method' => $this->method,
            'resource' => $this->resource,
            'constraints' => $this->constraints
                ->map(fn (PermissionConstraint $constraint): array => $constraint->toArray())
                ->values()
                ->all(),
        ];
    }
}
