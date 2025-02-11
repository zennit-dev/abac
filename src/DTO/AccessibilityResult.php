<?php

namespace zennit\ABAC\DTO;

use Illuminate\Database\Eloquent\Builder;
use JsonSerializable;

class AccessibilityResult implements JsonSerializable
{
    public function __construct(
        public Builder $filters,
    ) {
    }

    public function __toString(): string
    {
        return json_encode($this->jsonSerialize(), JSON_PRETTY_PRINT);
    }

    public function jsonSerialize(): array
    {
        return [
            'filters' => $this->filters->toSql(),
        ];
    }
}
