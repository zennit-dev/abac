<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteProvider extends ServiceProvider
{
    public function boot(): void
    {
        $config = config('abac.routes');

        Route::middleware($config['middleware'] ?? ['abac'])->group(function () use ($config) {
            // API routes with prefix
            if (file_exists(__DIR__ . '/../../routes/api.php')) {
                Route::prefix($config['prefix'] ?? 'abac')->group(function () {
                    $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
                });
            }

            // Web routes without prefix
            if (file_exists(__DIR__ . '/../../routes/web.php')) {
                $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
            }
        });
    }
}
