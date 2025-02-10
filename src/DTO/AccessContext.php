<?php

namespace zennit\ABAC\DTO;

use JsonSerializable;

class AccessContext implements JsonSerializable
{
    public function __construct(
        public string $resource,
        public string $operation,
        public object $subject,
        public ?array $context = []
    ) {
    }

    public function __toString(): string
    {
        return json_encode($this->jsonSerialize(), JSON_PRETTY_PRINT);
    }

    public function jsonSerialize(): array
    {
        return [
            'resource' => $this->resource,
            'operation' => $this->operation,
            'subject' => method_exists($this->subject, 'toArray')
                ? $this->subject->toArray()
                : get_object_vars($this->subject),
            'context' => $this->context,
        ];
    }
}
