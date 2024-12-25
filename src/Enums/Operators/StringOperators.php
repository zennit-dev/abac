<?php

namespace zennit\ABAC\Enums\Operators;

enum StringOperators: string
{
    case CONTAINS = 'contains';
    case NOT_CONTAINS = 'not_contains';
    case STARTS_WITH = 'starts_with';
    case NOT_STARTS_WITH = 'not_starts_with';
    case ENDS_WITH = 'ends_with';
    case NOT_ENDS_WITH = 'not_ends_with';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
