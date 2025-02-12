<?php

namespace zennit\ABAC\Enums;

enum PolicyMethod: string
{
    case READ = 'read';
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
