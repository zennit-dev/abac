<?php

return [
    'cache' => [
        'enabled' => env('AZENNIT_BAC_CACHE_ENABLED', true),
        'store' => env('ZENNIT_ABAC_CACHE_STORE', 'database'),
        'ttl' => env('ZENNIT_ABAC_CACHE_TTL', 3600),
        'prefix' => env('ZENNIT_ABAC_CACHE_PREFIX', 'zennit_abac_'),
        'warming' => [
            'enabled' => env('ZENNIT_ABAC_CACHE_WARMING_ENABLED', true),
            'schedule' => env('ZENNIT_ABAC_CACHE_WARMING_SCHEDULE', 'hourly'),
        ],
    ],
    'evaluation' => [
        'strict_validation' => env('ZENNIT_ABAC_STRICT_VALIDATION', true),
    ],
    'monitoring' => [
        'logging' => [
            'enabled' => env('ZENNIT_ABAC_LOGGING_ENABLED', true),
            'channel' => env('ZENNIT_ABAC_LOG_CHANNEL', 'zennit.abac'),
            'detailed' => env('ZENNIT_ABAC_DETAILED_LOGGING', false),
        ],
        'performance' => [
            'enabled' => env('ZENNIT_ABAC_PERFORMANCE_LOGGING_ENABLED', true),
            'slow_threshold' => env('ZENNIT_ABAC_SLOW_EVALUATION_THRESHOLD', 100),
        ],
        'events' => [
            'enabled' => env('ZENNIT_ABAC_EVENTS_ENABLED', true),
        ],
    ],
    'operators' => [
        'disabled' => [], // key => class name in the format of 'zennit\ABAC\Operators\OperatorName'
        'custom' => [], // key => class name in the format of 'zennit\ABAC\Operators\OperatorName'
    ],
    'database' => [
        'user_attribute_subject_type' => env('ZENNIT_ABAC_USER_ATTRIBUTE_SUBJECT_TYPE', 'users'),
    ],
];
