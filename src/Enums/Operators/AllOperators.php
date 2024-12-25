<?php

namespace zennit\ABAC\Enums\Operators;

use UnitEnum;

readonly class AllOperators
{
    /**
     * Get all operator values except the excluded ones
     *
     * @param  array<UnitEnum>  $excludes  Array of operator classes to exclude
     *
     * @return array<string> Array of operator values
     */
    public static function values(array $excludes = []): array
    {
        $operatorClasses = array_filter([
            LogicalOperators::class,
            ArithmeticOperators::class,
            ListOperators::class,
            StringOperators::class,
        ], fn ($class) => !in_array($class, $excludes));

        $operators = array_merge(
            ...array_map(
                fn ($class) => $class::cases(),
                $operatorClasses
            )
        );

        return array_map(fn ($operator) => $operator->value, $operators);
    }
}
