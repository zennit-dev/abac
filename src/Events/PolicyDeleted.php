<?php

namespace zennit\ABAC\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PolicyDeleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $policyId,
        public readonly array $metadata
    ) {
    }
}
