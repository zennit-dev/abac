<?php

namespace zennit\ABAC\Providers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Http\Middleware\EnsureAccess;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, class-string>
     */
    protected array $middlewareAliases = [
        'abac' => EnsureAccess::class,
    ];

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        /** @var Router $router */
        $router = $this->app->make('router');

        foreach ($this->middlewareAliases as $alias => $middleware) {
            $router->aliasMiddleware($alias, $middleware);
        }
    }
}
