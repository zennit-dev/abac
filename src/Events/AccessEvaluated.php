<?php

namespace zennit\ABAC\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccessEvaluated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly object $subject,
        public readonly string $resource,
        public readonly string $operation,
        public readonly array $resourceIds,
        public readonly bool $granted,
        public readonly array $context = []
    ) {
    }
}
