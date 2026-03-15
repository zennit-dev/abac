<?php

use zennit\ABAC\Support\AbacDefaults;

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
        'enabled' => env('ABAC_CACHE_ENABLED', AbacDefaults::CACHE_ENABLED),
        'store' => env('ABAC_CACHE_STORE', AbacDefaults::CACHE_STORE),
        'ttl' => env('ABAC_CACHE_TTL', AbacDefaults::CACHE_TTL),
        'prefix' => env('ABAC_CACHE_PREFIX', AbacDefaults::CACHE_PREFIX),
        'include_context' => env('ABAC_CACHE_INCLUDE_CONTEXT', AbacDefaults::CACHE_INCLUDE_CONTEXT),
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
            'enabled' => env('ABAC_LOGGING_ENABLED', AbacDefaults::LOGGING_ENABLED),
            'channel' => env('ABAC_LOG_CHANNEL', AbacDefaults::LOG_CHANNEL),
            'detailed' => env('ABAC_DETAILED_LOGGING', AbacDefaults::DETAILED_LOGGING),
        ],
        'performance' => [
            'enabled' => env('ABAC_PERFORMANCE_LOGGING_ENABLED', AbacDefaults::PERFORMANCE_LOGGING_ENABLED),
            'slow_threshold' => env('ABAC_SLOW_EVALUATION_THRESHOLD', AbacDefaults::SLOW_EVALUATION_THRESHOLD),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ABAC Database Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the settings for the ABAC database, including the
    | actor and resource additional attribute storage.
    |
    */

    'database' => [
        'actor_additional_attributes' => env('ABAC_ACTOR_ADDITIONAL_ATTRIBUTES', AbacDefaults::ACTOR_ADDITIONAL_ATTRIBUTES),
        'primary_key' => env('ABAC_PRIMARY_KEY', AbacDefaults::PRIMARY_KEY),
        'fallback_primary_key' => env('ABAC_FALLBACK_PRIMARY_KEY', AbacDefaults::FALLBACK_PRIMARY_KEY),
        'soft_deletes_column' => 'deleted_at',
    ],

    'policy' => [
        'default_policy_behavior' => env('ABAC_DEFAULT_POLICY_BEHAVIOR', AbacDefaults::DEFAULT_POLICY_BEHAVIOR),
    ],

    /*
    |--------------------------------------------------------------------------
    | ABAC Seeders Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the paths to the JSON files used by the ABAC seeders.
    | These paths are relative to the resources' directory.
    */
    'seeders' => [
        'actor_attribute_path' => 'stubs/abac/actor_attribute_path.json',
        'resource_attribute_path' => 'stubs/abac/resource_attribute_path.json',
        'policy_file_path' => 'stubs/abac/abac_policy_file_path.json',
    ],

    'permissions' => [
        'resources' => [], // alias => model class
    ],

    /*
    |--------------------------------------------------------------------------
    | ABAC Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the settings for ABAC middleware, including the
    | method to retrieve the actor and any excluded routes.
    |
    */

    'middleware' => [
        'actor_method' => env('ABAC_MIDDLEWARE_ACTOR_METHOD', AbacDefaults::ACTOR_METHOD),
        'excluded_routes' => [],
        'allow_if_unmatched_route' => env('ABAC_ALLOW_IF_UNMATCHED_ROUTE', AbacDefaults::ALLOW_IF_UNMATCHED_ROUTE),
        'resource_patterns' => [], // key => resource path eg.
    ],
];
