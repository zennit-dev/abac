<?php

namespace zennit\ABAC\Services\Evaluators;

use Illuminate\Database\Eloquent\Builder;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Models\AbacCheck;

readonly class AbacCheckEvaluator
{
    public function evaluate(Builder $query, AbacCheck $check, AccessContext $context): Builder
    {
        // Convert ABAC operator to SQL operator TODO: support all operators through early returns
        $operator = match ($check->operator) {
            'equals' => '=',
            'not_equals' => '<>',
            'greater_than' => '>',
            'less_than' => '<',
            'greater_than_equals' => '>=',
            'less_than_equals' => '<=',
            'contains' => 'LIKE',
            'not_contains' => 'NOT LIKE',
            default => '='
        };

        $value = $check->value;

        return $query->where($check->key, $operator, $value);
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