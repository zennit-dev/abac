<?php

namespace zennit\ABAC\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CacheWarmed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public int $count,
        public float $duration,
        public array $metadata = []
    ) {
    }
}
