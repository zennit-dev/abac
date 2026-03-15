<?php

namespace zennit\ABAC\Services\Evaluators;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\Operators\ArithmeticOperators;
use zennit\ABAC\Enums\Operators\StringOperators;
use zennit\ABAC\Models\AbacCheck;

readonly class AbacCheckEvaluator
{
    /**
     * @throws Exception
     */
    public function apply(Builder $query, AbacCheck $check, AccessContext $context): Builder
    {
        $actor = $context->actor;

        $type = (function () use ($check) {
            if (str_starts_with($check->key, 'resource.')) {
                return 'resource';
            }

            if (str_starts_with($check->key, 'actor.')) {
                return 'actor';
            }

            if (str_starts_with($check->key, 'environment.')) {
                return 'environment';
            }

            throw new Exception("Check of id $check->id has invalid key of type $check->key. Key must start with 'resource.', 'actor.', or 'environment.'");
        })();

        return match ($type) {
            'resource' => $this->applyConditionsToResource($query, $check),
            'actor' => $this->evaluateActorAccess($actor, $check) ? $query : $query->whereRaw('1 = 0'),
            'environment' => throw new Exception('Environment not implemented yet'),
            default => throw new Exception('Not implemented yet')
        };
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function applyConditionsToResource(Builder $query, AbacCheck $check): Builder
    {
        $key = str_replace('resource.', '', $check->key);

        $operator = match ($check->operator) {
            ArithmeticOperators::NOT_EQUALS->value => '<>',
            ArithmeticOperators::GREATER_THAN->value => '>',
            ArithmeticOperators::LESS_THAN->value => '<',
            ArithmeticOperators::GREATER_THAN_EQUALS->value => '>=',
            ArithmeticOperators::LESS_THAN_EQUALS->value => '<=',
            StringOperators::CONTAINS->value, StringOperators::ENDS_WITH->value, StringOperators::STARTS_WITH->value => 'LIKE',
            StringOperators::NOT_CONTAINS->value, StringOperators::NOT_STARTS_WITH->value, StringOperators::NOT_ENDS_WITH->value => 'NOT LIKE',
            default => '='
        };

        return match ($check->operator) {
            StringOperators::CONTAINS->value, StringOperators::NOT_CONTAINS->value => $query->where($key, $operator, "%$check->value%"),
            StringOperators::ENDS_WITH->value, StringOperators::NOT_ENDS_WITH->value => $query->where($key, $operator, "%$check->value"),
            StringOperators::STARTS_WITH->value, StringOperators::NOT_STARTS_WITH->value => $query->where($key, $operator, "$check->value%"),
            default => $query->where($key, $operator, $check->value),
        };
    }

    private function evaluateActorAccess(Model $model, AbacCheck $check): bool
    {

        $key = str_replace('actor.', '', $check->key);
        $value = data_get($model, $key);

        return match ($check->operator) {
            StringOperators::CONTAINS->value => str_contains($value, $check->value),
            StringOperators::NOT_CONTAINS->value => ! str_contains($value, $check->value),
            StringOperators::ENDS_WITH->value => str_ends_with($value, $check->value),
            StringOperators::NOT_ENDS_WITH->value => ! str_ends_with($value, $check->value),
            StringOperators::STARTS_WITH->value => str_starts_with($value, $check->value),
            StringOperators::NOT_STARTS_WITH->value => ! str_starts_with($value, $check->value),
            ArithmeticOperators::NOT_EQUALS->value => $value != $check->value,
            ArithmeticOperators::GREATER_THAN->value => $value > $check->value,
            ArithmeticOperators::LESS_THAN->value => $value < $check->value,
            ArithmeticOperators::GREATER_THAN_EQUALS->value => $value >= $check->value,
            ArithmeticOperators::LESS_THAN_EQUALS->value => $value <= $check->value,
            default => $value == $check->value,
        };
    }
}
