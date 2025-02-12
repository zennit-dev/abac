<?php

namespace zennit\ABAC\DTO;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use zennit\ABAC\Enums\PolicyMethod;

class AccessContext implements JsonSerializable
{
    /**
     * AccessContext - an object to hold all context of accessing a query
     *
     * @template TResource
     * @template TObject of Model
     *
     * @param  PolicyMethod  $method  The method being invoked
     * @param  Builder<TResource>  $subject  The query of the given resource
     * @param  TObject  $object  The accessor context (user, profile)
     * @param  array  $environment  Additional environment properties
     */
    public function __construct(
        public PolicyMethod $method,
        public Builder $subject,
        /**
         * @var TObject $object
         */
        public Model $object,
        public array $environment = [],
    ) {}

    public function __toString(): string
    {

        return json_encode($this->jsonSerialize(), JSON_PRETTY_PRINT);
    }

    public function jsonSerialize(): array
    {
        return [
            'method' => $this->method,
            'subject' => $this->subject->get()->toArray(),
            'object' => $this->object->toArray(),
            'environment' => $this->environment,
        ];
    }
}
