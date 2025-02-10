<?php

namespace zennit\ABAC\DTO;

use JsonSerializable;

class EvaluationResult implements JsonSerializable
{
    public function __construct(
        public bool $granted,
        public string $reason,
        public array $context = [],
        public array $matched = []
    ) {
    }

    public function __toString(): string
    {
        return json_encode($this->jsonSerialize(), JSON_PRETTY_PRINT);
    }

    public function jsonSerialize(): array
    {
        return [
            'granted' => $this->granted,
            'reason' => $this->reason,
            'context' => $this->context,
            'matched' => $this->matched,
        ];
    }
}
