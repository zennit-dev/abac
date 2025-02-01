<?php

namespace zennit\ABAC\Http\Policies\Core;

use Illuminate\Auth\Access\HandlesAuthorization;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Http\Controllers\Core\Controller;

/**
 * Abstract Policy class for all resources that make use of ABAC.
 *
 * This abstract class provides the foundation for all policy checks in the application,
 * implementing ABAC (Attribute-Based Access Control) authorization.
 *
 * The policy class should extend this class and implement the getResourceClass method,
 * which should return the fully qualified class name of the resource the policy governs.
 *
 * This class provides the following methods for checking permissions:
 * - create: Check if the profile can create new resources
 * - view: Check if the profile can view a specific resource
 * - update: Check if the profile can update a specific resource
 * - delete: Check if the profile can delete a specific resource
 *
 * Method viewAny is not implemented in the policy, but instead in the controller, as it is not a resource-specific operation.
 *
 * @see Controller::evaluateIndex()
 */
abstract class AbacPolicy
{
    use HandlesAuthorization;

    /**
     * Get the fully qualified class name of the resource this policy governs.
     *
     * @return string The fully qualified class name
     */
    abstract protected static function getResourceClass(): string;

    /**
     * Check if the profile can create new resources.
     *
     * @param  object  $subject  The profile attempting to create the resource
     *
     * @throws InvalidArgumentException When cache operations fail
     * @throws ValidationException When validation fails
     * @return bool Whether access is granted
     */
    public function create(object $subject): bool
    {
        return abacPolicy()->can(
            new AccessContext(
	            static::getResourceClass(),
	            PermissionOperations::CREATE->value,
	            $subject
            )
        );
    }

    /**
     * Check if the profile can view a specific resource.
     *
     * @param  object  $subject  The profile attempting to view the resource
     * @param  int  $modelId  The ID of the specific model being viewed
     *
     * @throws InvalidArgumentException When cache operations fail
     * @throws ValidationException When validation fails
     * @return bool Whether access is granted
     */
    public function view(object $subject, int $modelId): bool
    {
        return abacPolicy()->can(
            new AccessContext(
	            static::getResourceClass(),
	            PermissionOperations::SHOW->value,
	            $subject,
	            [$modelId]
            )
        );
    }

    /**
     * Check if the profile can update a specific resource.
     *
     * @param  object  $subject  The profile attempting to update the resource
     * @param  int  $modelId  The ID of the specific model being updated
     *
     * @throws InvalidArgumentException When cache operations fail
     * @throws ValidationException When validation fails
     * @return bool Whether access is granted
     */
    public function update(object $subject, int $modelId): bool
    {
        return abacPolicy()->can(
            new AccessContext(
	            static::getResourceClass(),
	            PermissionOperations::UPDATE->value,
	            $subject,
	            [$modelId]
            )
        );
    }

    /**
     * Check if the profile can delete a specific resource.
     *
     * @param  object  $subject  The profile attempting to delete the resource
     * @param  int  $modelId  The ID of the specific model being deleted
     *
     * @throws InvalidArgumentException When cache operations fail
     * @throws ValidationException When validation fails
     * @return bool Whether access is granted
     */
    public function delete(object $subject, int $modelId): bool
    {
        return abacPolicy()->can(
            new AccessContext(
	            static::getResourceClass(),
	            PermissionOperations::DELETE->value,
	            $subject,
	            [$modelId]
            )
        );
    }
}
