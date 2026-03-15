<?php

namespace zennit\ABAC\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use zennit\ABAC\Enums\PolicyMethod;

/**
 * @implements Arrayable<string, mixed>
 */
class AccessContext implements Arrayable, JsonSerializable
{
    /**
     * AccessContext - holds all context for access evaluation
     *
     * @param  PolicyMethod  $method  The method being invoked
     * @param  Builder<Model>  $resource  The query of the given resource
     * @param  Model  $actor  The accessor context (user, profile)
     * @param  array<string, mixed>  $environment  Additional environment properties
     */
    public function __construct(
        public PolicyMethod $method,
        public Builder $resource,
        public Model $actor,
        public array $environment = [],
    ) {}

    public function __toString(): string
    {
        $json = json_encode($this->jsonSerialize(), JSON_PRETTY_PRINT);

        return $json === false ? '{}' : $json;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'resource' => $this->resource,
            'actor' => $this->actor,
            'environment' => $this->environment,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $actor = $this->actor;

        return [
            'method' => $this->method,
            'resource' => $this->resource->get()->toArray(),
            'actor' => $actor->toArray(),
            'environment' => $this->environment,
        ];
    }
}
