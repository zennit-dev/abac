<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ABAC Cache Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the settings for the ABAC cache, including whether
    | caching is enabled, the cache store to use, the time-to-live (TTL) for
    | cache entries, and cache warming options.
    |
    */

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

    /*
    |--------------------------------------------------------------------------
    | ABAC Evaluation Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the settings for ABAC evaluation, including whether
    | strict validation is enabled.
    |
    */

    'evaluation' => [
        'strict_validation' => env('ABAC_STRICT_VALIDATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | ABAC Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the settings for ABAC monitoring, including logging
    | options, performance monitoring, and event handling.
    |
    */

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

    /*
    |--------------------------------------------------------------------------
    | ABAC Operators Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the settings for ABAC operators, including any
    | disabled or custom operators.
    |
    */

    'operators' => [
        'disabled' => [], // key => class name in the format of 'zennit\ABAC\Operators\OperatorName'
        'custom' => [], // key => class name in the format of 'zennit\ABAC\Operators\OperatorName'
    ],

    /*
    |--------------------------------------------------------------------------
    | ABAC Database Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the settings for the ABAC database, including the
    | user attribute subject type.
    |
    */

    'database' => [
        'user_attribute_subject_type' => env('ABAC_USER_ATTRIBUTE_SUBJECT_TYPE', 'users'),
        'user_soft_deletes_column' => 'deleted_at',
    ],

    /*
    |--------------------------------------------------------------------------
    | ABAC Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the settings for ABAC middleware, including the
    | method to retrieve the subject and any excluded routes.
    |
    */

    'middleware' => [
        'subject_method' => env('ABAC_MIDDLEWARE_SUBJECT_METHOD', 'user'),
        'excluded_routes' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | ABAC Route Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the settings for ABAC routes, including the
    | route prefix and any route middleware.
    |
    */

    'routes' => [
        'prefix' => env('ABAC_ROUTE_PREFIX', 'abac'),
        'middleware' => ['abac'],
    ],
];
