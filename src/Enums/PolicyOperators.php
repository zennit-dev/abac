<?php

namespace zennit\ABAC\Enums;

enum PolicyOperators: string
{
    case EQUALS = 'equals';
    case NOT_EQUALS = 'not_equals';
    case GREATER_THAN = 'greater_than';
    case LESS_THAN = 'less_than';
    case GREATER_THAN_EQUALS = 'greater_than_equals';
    case LESS_THAN_EQUALS = 'less_than_equals';
    case IN = 'in';
    case NOT_IN = 'not_in';
    case CONTAINS = 'contains';
    case NOT_CONTAINS = 'not_contains';
    case STARTS_WITH = 'starts_with';
    case NOT_STARTS_WITH = 'not_starts_with';
    case ENDS_WITH = 'ends_with';
    case NOT_ENDS_WITH = 'not_ends_with';
    case AND = 'and';
    case OR = 'or';
    case NOT = 'not';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
