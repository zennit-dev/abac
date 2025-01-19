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
use zennit\ABAC\Services\AbacCacheManager;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Traits\AbacHasConfigurations;

class AbacServiceProvider extends ServiceProvider
{
    use AbacHasConfigurations;

    /**
     * Register services and dependencies.
     */
    public function register(): void
    {
        $this->app->register(ConfigurationServiceProvider::class);
        $this->app->register(ServicesServiceProvider::class);
        $this->app->register(MiddlewareServiceProvider::class);
        $this->app->register(CommandServiceProvider::class);
        $this->app->register(EventServiceProvider::class);

        // Register the facade
        $this->app->bind('zennit.abac.facade', function ($app) {
            return $app->make(AbacService::class);
        });

        // Register the cache manager
        $this->app->bind('zennit.abac.cache', function ($app) {
            return $app->make(AbacCacheManager::class);
        });
    }

    /**
     * Bootstrap any application services.
     * Configures cache warming and observers if enabled.
     */
    public function boot(): void
    {
        if ($this->getCacheEnabled() && $this->getCacheWarmingEnabled()) {
            $this->registerCacheWarmingJob();
            $this->registerObservers();
        }
    }

    /**
     * Register the cache warming job with the scheduler.
     * Configures the job based on the cache warming schedule setting.
     */
    protected function registerCacheWarmingJob(): void
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $job = $schedule->job(new PolicyCacheJob('warm'))
                ->name('zennit_abac:warm-cache')
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

    /**
     * Register model observers for cache invalidation.
     * Sets up observers for all ABAC models to manage cache state.
     */
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
