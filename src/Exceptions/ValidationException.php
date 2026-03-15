<?php

namespace zennit\ABAC\Exceptions;

use Exception;

class ValidationException extends Exception
{
    public function __construct(string $message = 'Unsupported operator', int $code = 400, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
