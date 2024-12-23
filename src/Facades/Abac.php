<?php

namespace zennit\ABAC\Facades;

use Illuminate\Support\Facades\Facade;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\PolicyEvaluationResult;

/**
 * @method static bool can(AccessContext $context)
 * @method static PolicyEvaluationResult evaluate(AccessContext $context)
 */
class Abac extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'abac.facade';
    }
}
