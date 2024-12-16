<?php

namespace zennit\ABAC\Tests\Unit\Validators;

use stdClass;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Tests\TestCase;
use zennit\ABAC\Validators\AccessContextValidator;

class AccessContextValidatorTest extends TestCase
{
    private AccessContextValidator $validator;

    /**
     * @throws ValidationException
     */
    public function test_validates_valid_context(): void
    {
        $subject = new stdClass();
        $subject->id = 1;

        $context = new AccessContext(
            subject: $subject,
            resource: 'posts',
            operation: PermissionOperations::SHOW->value
        );

        $this->validator->validate($context);
        $this->assertTrue(true); // No exception thrown
    }

    public function test_throws_exception_for_missing_subject_id(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Subject must have an ID');

        $subject = new stdClass();
        $context = new AccessContext(
            subject: $subject,
            resource: 'posts',
            operation: PermissionOperations::SHOW->value
        );

        $this->validator->validate($context);
    }

    public function test_throws_exception_for_empty_resource(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Resource cannot be empty');

        $subject = new stdClass();
        $subject->id = 1;

        $context = new AccessContext(
            subject: $subject,
            resource: '',
            operation: PermissionOperations::SHOW->value
        );

        $this->validator->validate($context);
    }

    public function test_throws_exception_for_empty_operation(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Operation cannot be empty');

        $subject = new stdClass();
        $subject->id = 1;

        $context = new AccessContext(
            subject: $subject,
            resource: 'posts',
            operation: ''
        );

        $this->validator->validate($context);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new AccessContextValidator();
    }
}
