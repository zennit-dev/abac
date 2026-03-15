<?php

namespace zennit\ABAC\DTO;

use Illuminate\Database\Eloquent\Builder;

class AccessResult
{
    public function __construct(
        public Builder $query,
        public ?string $reason,
        public AccessContext $context,
        public bool $can,
    ) {}
}
