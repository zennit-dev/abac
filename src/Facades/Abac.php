<?php

namespace zennit\ABAC\Facades;

use Illuminate\Support\Facades\Facade;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\EvaluationResult;
use zennit\ABAC\Exceptions\ValidationException;

/**
 * Facade for the ABAC (Attribute-Based Access Control) service.
 *
 * Provides methods for evaluating access permissions based on
 * subject attributes, resource attributes, and operations.
 *
 * @method static bool can(AccessContext $context) Check if a subject has permission to perform an operation on a resource
 * @method static EvaluationResult evaluate(AccessContext $context) Evaluate an access request and return detailed results
 *
 * @throws ValidationException If the access context is invalid
 * @throws InvalidArgumentException If cache operations fail
 *
 * @see \zennit\ABAC\Services\AbacService
 */
class Abac extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'abac.facade';
    }
}
