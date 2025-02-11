<?php

namespace zennit\ABAC\DTO;

use JsonSerializable;

class AccessContext implements JsonSerializable
{
    public function __construct(
        public string $method,
        public array $subject,
        public array $object,
        public string $object_type,
        public string $subject_type,
    ) {
    }

    public function __toString(): string
    {
        return json_encode($this->jsonSerialize(), JSON_PRETTY_PRINT);
    }

    public function jsonSerialize(): array
    {
        return [
            'method' => $this->method,
            'subject' => $this->subject,
            'object' => $this->object,
        ];
    }
}
