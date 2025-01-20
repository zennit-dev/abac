<?php

namespace zennit\ABAC\Contracts;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\EvaluationResult;

interface AbacManager
{
    public function can(AccessContext $context): bool;

    public function evaluate(AccessContext $context): EvaluationResult;
}
