<?php

return [
    'cache' => [
        'enabled' => env('ABAC_CACHE_ENABLED', true),
        'store' => env('ABAC_CACHE_STORE', 'database'),
        'ttl' => env('ABAC_CACHE_TTL', 3600),
        'prefix' => env('ABAC_CACHE_PREFIX', 'abac_'),
        'warming' => [
            'enabled' => env('ABAC_CACHE_WARMING_ENABLED', true),
            'schedule' => env('ABAC_CACHE_WARMING_SCHEDULE', 'hourly'),
        ],
    ],
    'evaluation' => [
        'strict_validation' => env('ABAC_STRICT_VALIDATION', true),
    ],
    'monitoring' => [
        'logging' => [
            'enabled' => env('ABAC_LOGGING_ENABLED', true),
            'channel' => env('ABAC_LOG_CHANNEL', 'abac'),
            'detailed' => env('ABAC_DETAILED_LOGGING', false),
        ],
        'performance' => [
            'enabled' => env('ABAC_PERFORMANCE_LOGGING_ENABLED', true),
            'slow_threshold' => env('ABAC_SLOW_EVALUATION_THRESHOLD', 100),
        ],
        'events' => [
            'enabled' => env('ABAC_EVENTS_ENABLED', true),
        ],
    ],
    'operators' => [
        'disabled' => [], // key => class name in the format of 'zennit\ABAC\Operators\OperatorName'
        'custom' => [], // key => class name in the format of 'zennit\ABAC\Operators\OperatorName'
    ],
    'database' => [
        'user_attribute_subject_type' => env('ABAC_USER_ATTRIBUTE_SUBJECT_TYPE', 'users'),
    ],
    'middleware' => [
        'subject_method' => env('ABAC_MIDDLEWARE_SUBJECT_METHOD', 'user'),
        'excluded_routes' => [],
    ],
];
