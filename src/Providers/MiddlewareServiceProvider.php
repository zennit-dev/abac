<?php

namespace zennit\ABAC\Providers;

use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Http\Middleware\EnsureAccess;

class MiddlewareServiceProvider extends ServiceProvider
{
    protected array $middlewareAliases = [
        'abac' => EnsureAccess::class,
    ];

    public function boot(): void
    {
        $router = $this->app['router'];
        foreach ($this->middlewareAliases as $alias => $middleware) {
            $router->aliasMiddleware($alias, $middleware);
        }
    }
}
