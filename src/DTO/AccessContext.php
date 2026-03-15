<?php

namespace zennit\ABAC\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use zennit\ABAC\Enums\PolicyMethod;

class AccessContext implements Arrayable, JsonSerializable
{
    /**
     * AccessContext - holds all context for access evaluation
     *
     * @template TResource of Model
     * @template TActor of Model
     *
     * @param  PolicyMethod  $method  The method being invoked
     * @param  Builder<TResource>  $resource  The query of the given resource
     * @param  TActor  $actor  The accessor context (user, profile)
     * @param  array  $environment  Additional environment properties
     */
    public function __construct(
        public PolicyMethod $method,
        public Builder $resource,
        public Model $actor,
        public array $environment = [],
    ) {}

    public function __toString(): string
    {

        return json_encode($this->jsonSerialize(), JSON_PRETTY_PRINT);
    }

    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'resource' => $this->resource,
            'actor' => $this->actor,
            'environment' => $this->environment,
            'can' => $this->can ?? false,
        ];
    }

    public function jsonSerialize(): array
    {
        $actor = $this->actor;

        return [
            'method' => $this->method,
            'resource' => $this->resource->get()->toArray(),
            'actor' => $actor->toArray(),
            'environment' => $this->environment,
            'can' => $this->can ?? false,
        ];
    }
}
