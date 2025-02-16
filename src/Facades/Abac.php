<?php

namespace zennit\ABAC\Facades;

use BadMethodCallException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Contracts\AbacManager;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AccessResult;
use zennit\ABAC\Exceptions\ValidationException;

/**
 * Facade for the ABAC (Attribute-Based Access Control) service.
 *
 * Provides methods for evaluating access permissions based on
 * subject attributes, resource attributes, and operations.
 *
 * @method static bool can(AccessContext $context)
 *
 * @throws ValidationException If the access context is invalid
 * @throws InvalidArgumentException If cache operations fail
 * @throws BadMethodCallException If the method does not exist
 *
 * @see \zennit\ABAC\Services\AbacService
 */
class Abac extends Facade
{
    public static function __callStatic($method, $args)
    {
        $instance = static::resolveFacadeInstance(static::getFacadeAccessor());

        if (!method_exists($instance, $method)) {
            throw new BadMethodCallException("Method $method does not exist.");
        }

        return $instance->$method(...$args);
    }

    protected static function getFacadeAccessor(): string
    {
        return AbacManager::class;
    }

    /**
     * Register the ABAC routes.
     *
     * @param array $options Array of options, supports:
     *                      - middleware: string|array of middleware to apply
     *                      - prefix: string (optional) prefix for the routes, defaults to 'api'
     *
     * @return void
     */
    public static function routes(array $options = []): void
    {
        $middleware = $options['middleware'] ?? ['api'];
        $prefix = $options['prefix'] ?? 'api';

        Route::middleware($middleware)
            ->prefix($prefix)
            ->group(function () {
                require __DIR__ . '/../../routes/api.php';
            });
    }

    /**
     * Register the ABAC macros.
     *
     * - Request::abac(): AccessResult - Get the access result from the request.
     *
     * @return void
     *
     * @see \zennit\ABAC\Providers\RequestMacroProvider
     */
    public static function macros(): void
    {
        Request::macro('abac', function (): ?AccessResult {
            return $this->get('abac');
        });
    }
}
