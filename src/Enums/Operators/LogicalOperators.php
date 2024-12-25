<?php

namespace zennit\ABAC\Enums\Operators;

enum LogicalOperators: string
{
    case AND = 'and';
    case OR = 'or';
    case NOT = 'not';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
