<?php

namespace zennit\ABAC\DTO;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AccessResult
{
    /**
     * @param  Builder<Model>  $query
     */
    public function __construct(
        public Builder $query,
        public ?string $reason,
        public AccessContext $context,
        public bool $can,
    ) {}
}
