<?php

namespace zennit\ABAC\Enums\Operators;

enum ListOperators: string
{
    case IN = 'in';
    case NOT_IN = 'not_in';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
