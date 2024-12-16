<?php

namespace zennit\ABAC\Exceptions;

use Exception;

class PolicyEvaluationException extends Exception
{
    /**
     * PolicyEvaluationException constructor.
     */
    public function __construct(string $message = 'Policy Evaluation Error', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
