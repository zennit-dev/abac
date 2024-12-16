<?php

namespace zennit\ABAC\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CacheWarmed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $policiesCount,
        public readonly float $duration,
        public readonly array $metadata = []
    ) {
    }
}
