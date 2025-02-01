<?php

namespace zennit\ABAC\Enums;

enum RequestMethods: string
{
    case INDEX = 'index';
    case SHOW = 'show';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';

    public static function isValid(string $operation): bool
    {
        return in_array($operation, self::values(), true);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
