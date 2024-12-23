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

    /**
     * Get the next scheduled warming time
     */
    public function getNextWarming(): ?string
    {
        return $this->metadata['next_warming'] ?? null;
    }

    /**
     * Get the cache TTL used for this warming
     */
    public function getTTL(): ?int
    {
        return $this->metadata['ttl'] ?? null;
    }

    /**
     * Check if this was a partial cache warming
     */
    public function isPartialWarming(): bool
    {
        return isset($this->metadata['resource']);
    }

    /**
     * Get the resource if this was a partial warming
     */
    public function getResource(): ?string
    {
        return $this->metadata['resource'] ?? null;
    }
}
