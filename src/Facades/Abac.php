<?php

namespace zennit\ABAC\Facades;

use Illuminate\Support\Facades\Facade;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\EvaluationResult;

/**
 * @method static bool can(AccessContext $context)
 * @method static EvaluationResult evaluate(AccessContext $context)
 */
class Abac extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'abac.facade';
    }
}
