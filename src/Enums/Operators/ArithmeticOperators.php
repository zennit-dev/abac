<?php

namespace zennit\ABAC\Enums\Operators;

enum ArithmeticOperators: string
{
    case EQUALS = 'equals';
    case NOT_EQUALS = 'not_equals';
    case GREATER_THAN = 'greater_than';
    case LESS_THAN = 'less_than';
    case GREATER_THAN_EQUALS = 'greater_than_equals';
    case LESS_THAN_EQUALS = 'less_than_equals';
    case NOT = 'not';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
