<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Middleware\EnsurePermissions;

class MiddlewareServiceProvider extends ServiceProvider
{
    protected array $middlewareAliases = [
        'zennit.abac' => EnsurePermissions::class,
        'zennit.abac.permissions' => EnsurePermissions::class,
    ];

    public function boot(): void
    {
        $router = $this->app['router'];
        foreach ($this->middlewareAliases as $alias => $middleware) {
            $router->aliasMiddleware($alias, $middleware);
        }
    }
}
