<?php

namespace zennit\ABAC\Exceptions;

use Exception;

class ValidationException extends Exception
{
    public function __construct($message = 'Validation failed', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
