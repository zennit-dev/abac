<?php

namespace zennit\ABAC\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\Permission;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Models\PolicyCollection;
use zennit\ABAC\Models\PolicyCondition;
use zennit\ABAC\Models\PolicyConditionAttribute;
use zennit\ABAC\Models\ResourceAttribute;
use zennit\ABAC\Models\UserAttribute;
use zennit\ABAC\Observers\PermissionObserver;
use zennit\ABAC\Observers\PolicyCollectionObserver;
use zennit\ABAC\Observers\PolicyConditionAttributeObserver;
use zennit\ABAC\Observers\PolicyConditionObserver;
use zennit\ABAC\Observers\PolicyObserver;
use zennit\ABAC\Observers\ResourceAttributeObserver;
use zennit\ABAC\Observers\UserAttributeObserver;
use zennit\ABAC\Services\ZennitAbacService;
use zennit\ABAC\Traits\ZennitAbacHasConfigurations;

class AbacServiceProvider extends ServiceProvider
{
    use ZennitAbacHasConfigurations;

    public function register(): void
    {
        $this->app->register(ConfigurationServiceProvider::class);
        $this->app->register(ServicesServiceProvider::class);
        $this->app->register(MiddlewareServiceProvider::class);
        $this->app->register(CommandServiceProvider::class);

        // Register the facade
        $this->app->bind('abac.facade', function ($app) {
            return $app->make(ZennitAbacService::class);
        });
    }

    public function boot(): void
    {
        if ($this->getCacheEnabled() && $this->getCacheWarmingEnabled()) {
            $this->registerCacheWarmingJob();
            $this->registerObservers();
        }
    }

    protected function registerCacheWarmingJob(): void
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $job = $schedule->job(new PolicyCacheJob('warm'))
                ->name('abac:warm-cache')
                ->withoutOverlapping();

            $scheduleType = $this->getCacheWarmingSchedule();

            match ($scheduleType) {
                'daily' => $job->daily(),
                'weekly' => $job->weekly(),
                'monthly' => $job->monthly(),
                default => $job->hourly(),
            };
        });
    }

    protected function registerObservers(): void
    {
        ResourceAttribute::observe(ResourceAttributeObserver::class);
        UserAttribute::observe(UserAttributeObserver::class);
        Permission::observe(PermissionObserver::class);
        Policy::observe(PolicyObserver::class);
        PolicyCollection::observe(PolicyCollectionObserver::class);
        PolicyCondition::observe(PolicyConditionObserver::class);
        PolicyConditionAttribute::observe(PolicyConditionAttributeObserver::class);
    }
}
