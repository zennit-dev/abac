<?php

namespace zennit\ABAC\Services\Evaluators;

use Illuminate\Database\Eloquent\Builder;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\Operators\ArithmeticOperators;
use zennit\ABAC\Enums\Operators\StringOperators;
use zennit\ABAC\Models\AbacCheck;

readonly class AbacCheckEvaluator
{
    public function evaluate(Builder $query, AbacCheck $check, AccessContext $context): Builder
    {
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
            StringOperators::CONTAINS->value, StringOperators::NOT_CONTAINS->value => $query->where($check->key, $operator, "%$check->value%"),
            StringOperators::ENDS_WITH->value, StringOperators::NOT_ENDS_WITH->value => $query->where($check->key, $operator, "%$check->value"),
            StringOperators::STARTS_WITH->value, StringOperators::NOT_STARTS_WITH->value => $query->where($check->key, $operator, "$check->value%"),
            default => $query->where($check->key, $operator, $check->value),
        };
    }
}


//// Use a CASE statement to check both tables with proper precedence
//return $query->where(function ($subQuery) use ($check, $operator, $value, $table, $additionalTable) {
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
//});
