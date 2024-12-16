<?php

namespace zennit\ABAC\DTO;

readonly class AccessContext
{
    public function __construct(
        public object $subject,
        public string $resource,
        public string $operation,
        public array $resourceIds = []
    ) {
    }

    public function user(): object
    {
        return $this->subject;
    }
}
