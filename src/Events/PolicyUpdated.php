<?php

namespace zennit\ABAC\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use zennit\ABAC\Models\Policy;

class PolicyUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Policy $policy,
        public readonly array $changes
    ) {
    }
}
