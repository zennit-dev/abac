<?php

return [
    'cache' => [
        'enabled' => env('ABAC_CACHE_ENABLED', true),
        'ttl' => env('ABAC_CACHE_TTL', 3600),
        'tags' => ['abac', 'abac-policies', 'abac-attributes'],
        'prefix' => 'abac:',
    ],
    'logging' => [
        'enabled' => env('ABAC_LOGGING_ENABLED', true),
        'channel' => env('ABAC_LOG_CHANNEL', 'abac'),
        'detailed' => env('ABAC_DETAILED_LOGGING', false),
        'events' => [
            'access_evaluated' => env('ABAC_LOG_ACCESS_EVALUATED', true),
            'policy_changes' => env('ABAC_LOG_POLICY_CHANGES', true),
            'cache_operations' => env('ABAC_LOG_CACHE_OPERATIONS', false),
        ],
    ],
    'performance' => [
        'batch_size' => env('ABAC_BATCH_SIZE', 1000),
        'cache_warming_enabled' => env('ABAC_CACHE_WARMING_ENABLED', true),
        'parallel_evaluation' => env('ABAC_PARALLEL_EVALUATION', false),
        'logging_enabled' => env('ABAC_PERFORMANCE_LOGGING', true),
        'thresholds' => [
            'slow_evaluation' => env('ABAC_SLOW_EVALUATION_THRESHOLD', 100), // ms
            'batch_chunk_size' => env('ABAC_BATCH_CHUNK_SIZE', 100),
        ],
    ],
    'user_attributes' => [
        'foreign_key' => env('ABAC_USER_ATTRIBUTES_FOREIGN_KEY', 'userId'),
        'constrained_table' => env('ABAC_USER_ATTRIBUTES_CONSTRAINED_TABLE', 'users'),
    ],
    'operators' => [
        'custom_operators' => [], // Allow registration of custom operators
        'disabled_operators' => [], // Disable specific operators
    ],
    'validation' => [
        'strict_mode' => env('ABAC_STRICT_VALIDATION', true),
        'required_attributes' => [], // Define required attributes per resource
    ],
    'events' => [
        'enabled' => env('ABAC_EVENTS_ENABLED', true),
        'async' => env('ABAC_ASYNC_EVENTS', false),
    ],
    'tables' => [
        'user_attributes' => [
            'name' => 'user_attributes',
            'subject_type_column' => 'subject_type',
            'subject_id_column' => 'subject_id',
            'attribute_name_column' => 'attribute_name',
            'attribute_value_column' => 'attribute_value',
        ],
        'resource_attributes' => [
            'name' => 'resource_attributes',
            'resource_column' => 'resource',
            'attribute_name_column' => 'attribute_name',
            'attribute_value_column' => 'attribute_value',
        ],
    ],

    'models' => [
        'user_attribute' => \zennit\ABAC\Models\UserAttribute::class,
        'resource_attribute' => \zennit\ABAC\Models\ResourceAttribute::class,
    ],
];
