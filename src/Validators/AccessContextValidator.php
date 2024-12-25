<?php

namespace zennit\ABAC\Validators;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Exceptions\ValidationException;

readonly class AccessContextValidator
{
    /**
     * Validate an access context.
     *
     * @param AccessContext $context The context to validate
     * @throws ValidationException If validation fails
     */
    public function validate(AccessContext $context): void
    {
        $this->validateSubject($context);
        $this->validateResource($context->resource);
        $this->validateOperation($context->operation);
    }

    /**
     * Validate the subject in an access context.
     *
     * @param AccessContext $context The context containing the subject
     * @throws ValidationException If subject is invalid
     */
    private function validateSubject(AccessContext $context): void
    {
        if (!isset($context->subject)) {
            throw new ValidationException('Subject is required');
        }

        if (!isset($context->subject->id)) {
            throw new ValidationException('Subject must have an ID');
        }
    }

    /**
     * Validate a resource string.
     *
     * @param string $resource The resource to validate
     * @throws ValidationException If resource is empty
     */
    private function validateResource(string $resource): void
    {
        if (empty($resource)) {
            throw new ValidationException('Resource cannot be empty');
        }
    }

    /**
     * Validate an operation string.
     *
     * @param string $operation The operation to validate
     * @throws ValidationException If operation is empty
     */
    private function validateOperation(string $operation): void
    {
        if (empty($operation)) {
            throw new ValidationException('Operation cannot be empty');
        }
    }
}
