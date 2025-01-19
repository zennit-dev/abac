<?php

namespace zennit\ABAC\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use zennit\ABAC\Events\CacheWarmed;
use zennit\ABAC\Listeners\CacheWarmingCompletedListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CacheWarmed::class => [
            CacheWarmingCompletedListener::class,
        ],
    ];
}
