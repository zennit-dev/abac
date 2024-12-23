<?php

namespace zennit\ABAC\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Jobs\PolicyCacheJob;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Traits\HasConfigurations;
use zennit\ABAC\Models\{
    Permission,
    Policy,
    PolicyCondition,
    PolicyConditionAttribute,
    ResourceAttribute,
    UserAttribute
};
use zennit\ABAC\Observers\{
    PermissionObserver,
    PolicyObserver,
    PolicyConditionObserver,
    PolicyConditionAttributeObserver,
    ResourceAttributeObserver,
    UserAttributeObserver
};

class AbacServiceProvider extends ServiceProvider
{
    use HasConfigurations;

    public function register(): void
    {
        $this->app->register(ConfigurationServiceProvider::class);
        $this->app->register(ServicesServiceProvider::class);
        $this->app->register(MiddlewareServiceProvider::class);
        $this->app->register(CommandServiceProvider::class);

        // Register the facade
        $this->app->bind('abac.facade', function ($app) {
            return $app->make(AbacService::class);
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
        Permission::observe(PermissionObserver::class);
        Policy::observe(PolicyObserver::class);
        PolicyCondition::observe(PolicyConditionObserver::class);
        PolicyConditionAttribute::observe(PolicyConditionAttributeObserver::class);
        ResourceAttribute::observe(ResourceAttributeObserver::class);
        UserAttribute::observe(UserAttributeObserver::class);
    }
}
