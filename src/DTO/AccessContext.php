<?php

namespace zennit\ABAC\DTO;

class AccessContext
{
    public function __construct(
        public string $resource,
        public string $operation,
        public mixed $subject,
        public ?array $context = []
    ) {
    }

    public function user(): object
    {
        return $this->subject;
    }
}
