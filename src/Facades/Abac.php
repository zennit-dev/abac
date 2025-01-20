<?php

namespace zennit\ABAC\Facades;

use BadMethodCallException;
use Illuminate\Support\Facades\Facade;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Contracts\AbacManager;
use zennit\ABAC\Exceptions\ValidationException;

/**
 * Facade for the ABAC (Attribute-Based Access Control) service.
 *
 * Provides methods for evaluating access permissions based on
 * subject attributes, resource attributes, and operations.
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
}
