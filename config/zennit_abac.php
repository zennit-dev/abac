<?php

return [
    'cache' => [
        'enabled' => env('ABAC_CACHE_ENABLED', true),
        'ttl' => env('ABAC_CACHE_TTL', 3600),
        'tags' => ['zennit.abac', 'zennit.abac-policies', 'zennit.abac-attributes'],
        'prefix' => 'zennit:abac:',
        'warming' => [
            'enabled' => env('ABAC_CACHE_WARMING_ENABLED', true),
            'chunk_size' => env('ABAC_CACHE_WARMING_CHUNK_SIZE', 100),
            'schedule' => env('ABAC_CACHE_WARMING_SCHEDULE', 'hourly'),
        ],
    ],
    'evaluation' => [
        'parallel' => env('ABAC_PARALLEL_EVALUATION', false),
        'batch_size' => env('ABAC_BATCH_SIZE', 1000),
        'chunk_size' => env('ABAC_BATCH_CHUNK_SIZE', 100),
        'strict_validation' => env('ABAC_STRICT_VALIDATION', true),
    ],
    'monitoring' => [
        'logging' => [
            'enabled' => env('ABAC_LOGGING_ENABLED', true),
            'channel' => env('ABAC_LOG_CHANNEL', 'zennit.abac'),
            'detailed' => env('ABAC_DETAILED_LOGGING', false),
        ],
        'performance' => [
            'enabled' => env('ABAC_PERFORMANCE_LOGGING', true),
            'slow_threshold' => env('ABAC_SLOW_EVALUATION_THRESHOLD', 100),
        ],
        'events' => [
            'enabled' => env('ABAC_EVENTS_ENABLED', true),
            'async' => env('ABAC_ASYNC_EVENTS', false),
        ],
    ],
    'operators' => [
        'disabled' => [], // key => class name in the format of 'zennit\ABAC\Operators\OperatorName'
        'custom' => [], // key => class name in the format of 'zennit\ABAC\Operators\OperatorName'
    ],
    'database' => [
        'user_attribute_subject_type' => env('ABAC_USER_ATTRIBUTE_SUBJECT_TYPE', 'App\\Models\\User'),
    ],
];
