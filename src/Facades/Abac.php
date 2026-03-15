<?php

namespace zennit\ABAC\Facades;

use BadMethodCallException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Contracts\AbacManager;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AccessResult;
use zennit\ABAC\DTO\PermissionGrant;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Services\AbacService;

/**
 * Facade for the ABAC (Attribute-Based Access Control) service.
 *
 * Provides methods for evaluating access permissions and managing grants.
 *
 * @method static bool can(AccessContext $context)
 * @method static PermissionGrant addPermission(string $method, string $resource, array<string, mixed>|array<int, array<string, mixed> >|string $constraints)
 * @method static Collection<int, PermissionGrant> getPermissions(?string $method = null, ?string $resource = null, array<string, mixed> $filters = [])
 * @method static PermissionGrant|null getPermission(int $grantId)
 * @method static PermissionGrant updatePermission(int $grantId, array<string, mixed>|array<int, array<string, mixed> >|string $constraints)
 * @method static bool removePermission(int $grantId)
 * @method static int removePermissions(string $method, string $resource, array<string, mixed>|array<int, array<string, mixed> >|string|null $constraints = null)
 *
 * @throws ValidationException If the access context is invalid
 * @throws InvalidArgumentException If cache operations fail
 * @throws BadMethodCallException If the method does not exist
 *
 * @see AbacService
 *
 * @mixin Request
 */
class Abac extends Facade
{
    /**
     * @param  string  $method
     * @param  array<int, mixed>  $args
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::resolveFacadeInstance(static::getFacadeAccessor());

        if (! method_exists($instance, $method)) {
            throw new BadMethodCallException("Method $method does not exist.");
        }

        return $instance->$method(...$args);
    }

    protected static function getFacadeAccessor(): string
    {
        return AbacManager::class;
    }

    /**
     * Register the ABAC macros.
     *
     *
     * @see Request::abac()
     */
    public static function macros(): void
    {
        /**
         * Get the access result from the request.
         *
         * @return AccessResult|null
         */
        Request::macro('abac', function (): ?AccessResult {
            return $this->input('abac');
        });
    }
}
