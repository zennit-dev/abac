<?php

namespace zennit\ABAC\Models\Concerns;

use Throwable;
use zennit\ABAC\Services\AbacCacheManager;

trait FlushesAbacCache
{
    protected static function registerAbacCacheFlushHooks(): void
    {
        $flushCache = static function (): void {
            try {
                if (! config('abac.cache.flush_on_write', true)) {
                    return;
                }

                if (app()->bound(AbacCacheManager::class)) {
                    app(AbacCacheManager::class)->flush();
                }
            } catch (Throwable) {
            }
        };

        static::created($flushCache);
        static::updated($flushCache);
        static::deleted($flushCache);
    }
}
