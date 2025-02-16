<?php

namespace zennit\ABAC\Services\Evaluators;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\Operators\ArithmeticOperators;
use zennit\ABAC\Enums\Operators\StringOperators;
use zennit\ABAC\Models\AbacCheck;

readonly class AbacCheckEvaluator
{
    public function apply(Builder $query, AbacCheck $check, AccessContext $context): Builder
    {
        /** @var "subject" | "object" | "environment" $type */
        $type = (function () use ($check) {
            if (str_contains($check->key, 'subject')) {
                return 'subject';
            }

            if (str_contains($check->key, 'object.')) {
                return 'object';
            }

            if (str_contains($check->key, 'environment.')) {
                return 'environment';
            }

            throw new \Exception("Check of id $check->id has invalid key of type $check->key. Key must start with 'subject.', 'object.', 'environment.' or 'environment.' ");
        })();

        return match ($type) {
            'subject' => $this->applyConditionsToSubject($query, $check, $context),
            // basically emptying the query
            'object' => $this->evaluateObjectAccess($context->object, $check, $context) ? $query : $query->whereRaw('1 = 0'),
            'environment' => throw new \Exception('Environment not implemented yet'),
            default =>  throw new \Exception('Not implemented yet')
        };
    }

    private function applyConditionsToSubject(Builder $query, AbacCheck $check, AccessContext $context): Builder
    {
        $key = str_replace('subject.', '', $check->key);

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

    private function evaluateObjectAccess(Model $model, AbacCheck $check, AccessContext $context): bool
    {

        $key = str_replace('object.', '', $check->key);
        $value = data_get($model, $key);

        return match ($check->operator) {
            StringOperators::CONTAINS->value => str_contains($value, $check->value),
            StringOperators::NOT_CONTAINS->value => !str_contains($value, $check->value),
            StringOperators::ENDS_WITH->value => str_ends_with($value, $check->value),
            StringOperators::NOT_ENDS_WITH->value => !str_ends_with($value, $check->value),
            StringOperators::STARTS_WITH->value => str_starts_with($value, $check->value),
            StringOperators::NOT_STARTS_WITH->value => !str_starts_with($value, $check->value),
            ArithmeticOperators::EQUALS->value => $value == $check->value,
            ArithmeticOperators::NOT_EQUALS->value => $value != $check->value,
            ArithmeticOperators::GREATER_THAN->value => $value > $check->value,
            ArithmeticOperators::LESS_THAN->value => $value < $check->value,
            ArithmeticOperators::GREATER_THAN_EQUALS->value => $value >= $check->value,
            ArithmeticOperators::LESS_THAN_EQUALS->value => $value <= $check->value,
            default => $value == $check->value,
        };
    }
}

// // Use a CASE statement to check both tables with proper precedence
// return $query->where(function ($subQuery) use ($check, $operator, $value, $table, $additionalTable) {
//    $subQuery->where(function ($q) use ($check, $operator, $value, $table, $additionalTable) {
//        $q->whereExists(function ($exists) use ($check, $operator, $value, $table, $additionalTable) {
//            // Check additional attributes table first
//            $exists->select(DB::raw(1))
//                ->from($additionalTable)
//                ->whereColumn("$additionalTable._id", "$table.id")
//                ->where("$additionalTable.subject", get_class($q->getModel()))
//                ->where("$additionalTable.key", $check->key)
//                ->where("$additionalTable.value", $operator, $value);
//        });
//    })->orWhere(function ($q) use ($check, $operator, $value, $table, $additionalTable) {
//        // If no override exists, check the original column
//        $q->where("$table.{$check->key}", $operator, $value)
//            ->whereNotExists(function ($exists) use ($check, $table, $additionalTable) {
//                $exists->select(DB::raw(1))
//                    ->from($additionalTable)
//                    ->whereColumn("$additionalTable._id", "$table.id")
//                    ->where("$additionalTable.subject", get_class($q->getModel()))
//                    ->where("$additionalTable.key", $check->key);
//            });
//    });
// });
