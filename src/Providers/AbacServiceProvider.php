<?php

namespace zennit\ABAC\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Contracts\AbacManager;
use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacCheck;
use zennit\ABAC\Models\AbacObjectAdditionalAttributes;
use zennit\ABAC\Models\AbacPolicy;
use zennit\ABAC\Models\AbacSubjectAdditionalAttribute;
use zennit\ABAC\Observers\AbacChainObserver;
use zennit\ABAC\Observers\AbacCheckObserver;
use zennit\ABAC\Observers\AbacObjectAdditionalAttributeObserver;
use zennit\ABAC\Observers\AbacPolicyObserver;
use zennit\ABAC\Observers\AbacSubjectAdditionalAttributeObserver;
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
        $this->app->register(RouteProvider::class);

        // Register the facade
        $this->app->bind(AbacManager::class, function ($app) {
            return $app->make(AbacService::class);
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

    /**
     * Register model observers for cache invalidation.
     * Sets up observers for all ABAC models to manage cache state.
     */
    protected function registerObservers(): void
    {
        AbacSubjectAdditionalAttribute::observe(AbacSubjectAdditionalAttributeObserver::class);
        AbacObjectAdditionalAttributes::observe(AbacObjectAdditionalAttributeObserver::class);
        AbacPolicy::observe(AbacPolicyObserver::class);
        AbacChain::observe(AbacChainObserver::class);
        AbacCheck::observe(AbacCheckObserver::class);
    }
}
