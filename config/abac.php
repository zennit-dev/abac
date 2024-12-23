<?php

return [
    'cache' => [
        'enabled' => env('ABAC_CACHE_ENABLED', true),
        'ttl' => env('ABAC_CACHE_TTL', 3600),
        'tags' => ['abac', 'abac-policies', 'abac-attributes'],
        'prefix' => 'abac:',
        'warming' => [
            'enabled' => env('ABAC_CACHE_WARMING_ENABLED', true),
            'chunk_size' => env('ABAC_CACHE_WARMING_CHUNK_SIZE', 100),
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
            'channel' => env('ABAC_LOG_CHANNEL', 'abac'),
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
    'database' => [
        'subject_type' => env('ABAC_SUBJECT_TYPE', 'users'),
        'subject_id' => env('ABAC_SUBJECT_ID', 'id'),
    ],
];
