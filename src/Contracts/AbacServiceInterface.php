<?php

namespace zennit\ABAC\Contracts;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\PolicyEvaluationResult;

interface AbacServiceInterface
{
    public function evaluate(AccessContext $context): PolicyEvaluationResult;
}
