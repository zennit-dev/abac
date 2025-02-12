<?php

namespace zennit\ABAC\DTO;

use Illuminate\Database\Eloquent\Builder;

class AccessResult
{
    public bool $can;

    public function __construct(
        public Builder $query,
        public ?string $reason,
        public AccessContext $context
    ) {
        $this->can = $this->query->count() > 0;
    }
}
