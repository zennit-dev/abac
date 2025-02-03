<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteProvider extends ServiceProvider
{
    public function boot(): void
    {
        $config = config('abac.routes');

        // Convert middleware string to array if it's a string
        $middleware = $config['middleware'];
        if (is_string($middleware)) {
            $middleware = array_filter(explode(',', $middleware));
        }

        Route::middleware($middleware)->group(function () use ($config) {
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
