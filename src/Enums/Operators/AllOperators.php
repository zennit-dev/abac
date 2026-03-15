<?php

namespace zennit\ABAC\Enums\Operators;

readonly class AllOperators
{
    /**
     * Get all operator values except the excluded ones
     *
     * @param  list<class-string>  $excludes
     * @return list<string>
     */
    public static function values(array $excludes = []): array
    {
        /** @var list<class-string> $operatorClasses */
        $operatorClasses = array_values(array_filter([
            LogicalOperators::class,
            ArithmeticOperators::class,
            StringOperators::class,
        ], fn (string $class): bool => ! in_array($class, $excludes, true)));

        $operators = array_merge(
            ...array_map(
                fn (string $class): array => $class::cases(),
                $operatorClasses
            )
        );

        return array_values(array_map(fn ($operator): string => $operator->value, $operators));
    }
}
