<?php

namespace zennit\ABAC\DTO;

class EvaluationResult
{
    public function __construct(
        public bool $granted,
        public string $reason,
        public array $context = [],
        public array $matched = []
    ) {}
}
