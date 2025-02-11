<?php

namespace zennit\ABAC\DTO;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class EmptyObject implements JsonSerializable, Arrayable
{
    public function toArray(): array
    {
        return[];
    }

    public function jsonSerialize(): array
    {
        return [];
    }
}
