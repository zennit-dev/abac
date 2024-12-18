<?php

namespace zennit\ABAC\Exceptions;

use Exception;

class UnsupportedOperatorException extends Exception
{
    public function __construct($message = 'Unsupported operator', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
