<?php

namespace zennit\ABAC\DTO;

class AccessContext
{
    public function __construct(
        public string $resource,
        public string $operation,
        public object $subject,
        public ?array $context = []
    ) {
    }
}
