<?php

namespace zennit\ABAC\Listeners;

use Illuminate\Support\Facades\Log;
use zennit\ABAC\Events\CacheWarmed;

class CacheWarmingCompletedListener
{
    public function handle(CacheWarmed $event): void
    {
        Log::info('Cache warming completed', [
            'policies_count' => $event->count,
            'duration' => round($event->duration, 2) . 's',
            'metadata' => $event->metadata,
        ]);
    }
}
