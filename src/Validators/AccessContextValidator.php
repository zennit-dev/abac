<?php

namespace zennit\ABAC\Validators;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Exceptions\ValidationException;

class AccessContextValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(AccessContext $context): void
    {
        $this->validateSubject($context);
        $this->validateResource($context->resource);
        $this->validateOperation($context->operation);
    }

    /**
     * @throws ValidationException
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
     * @throws ValidationException
     */
    private function validateResource(string $resource): void
    {
        if (empty($resource)) {
            throw new ValidationException('Resource cannot be empty');
        }
    }

    /**
     * @throws ValidationException
     */
    private function validateOperation(string $operation): void
    {
        if (empty($operation)) {
            throw new ValidationException('Operation cannot be empty');
        }
    }
}
