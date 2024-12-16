<?php

namespace zennit\ABAC\Facades;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Facade;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\PolicyEvaluationResult;

class Abac extends Facade
{
    /**
     * @throws BindingResolutionException
     */
    public static function evaluate(AccessContext $context): PolicyEvaluationResult
    {
        return static::$app->make('abac')->evaluate($context);
    }

    /**
     * @throws BindingResolutionException
     */
    public static function can(AccessContext $context): bool
    {
        return static::$app->make('abac')->evaluate($context)->granted;
    }

    public static function getFacadeAccessor(): string
    {
        return 'abac';
    }
}
