<?php

namespace zennit\ABAC\Contracts;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\PolicyEvaluationResult;

interface PolicyEvaluatorInterface
{
    public function can(AccessContext $context): bool;

    public function evaluate(AccessContext $context): PolicyEvaluationResult;
}
