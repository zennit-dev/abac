# ABAC (Attribute-Based Access Control) for Laravel

A flexible and powerful ABAC implementation for Laravel applications.

---

## Table of Contents

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Quick Start](#quick-start)
4. [Basic Usage](#basic-usage)
5. [API Routes](#api-routes)
6. [Commands](#commands)
7. [Configuration](#configuration)
8. [Operators](#operators)
9. [Context Value Resolution](#context-value-resolution)
10. [Database Schema](#database-schema)
11. [Models](#models)
12. [License](#license)
13. [Contributing](#contributing)

---

## Introduction

ABAC provides fine-grained access control by evaluating attributes of users, resources, and the context of a request.

This package integrates seamlessly with Laravel to offer powerful access control mechanisms.

---

## Installation

Install the package via Composer:

```bash
composer require zennit/abac
```

Add the service provider to your `bootstrap/providers.php`:

```php
return [
    # ... other providers
    zennit\ABAC\Providers\AbacServiceProvider::class,
];
```

---

## Quick Start

1. Publish configuration and migrations:
   ```bash
   php artisan abac:publish
   ```

2. Run migrations:
   ```bash
   php artisan migrate
   ```

3. Create permissions using JSON:
   ```json
    "object_attributes": [{
        "object_id": 1,
        "attribute_name": "owner_id",
        "attribute_value": "5"
    }],
    "subject_attributes": [{
        "subject": "App\\Models\\User",
        "subject_id": 1,
        "attribute_name": "role",
        "attribute_value": "admin"
    }],
    "policies": [{
        "resource": "posts",
        "method": "view",
        "chains": [{
            "operator": "and",
            "chains": [
                {
                    "operator": "or",
                    "chains": [
                        {
                            "operator": "and",
                            "chains": [{
                                "operator": "or",
                                "checks": [
                                    {
                                        "operator": "greater_than",
                                        "context_accessor": "object.view_count",
                                        "value": "1000"
                                    },
                                    {
                                        "operator": "contains",
                                        "context_accessor": "object.title",
                                        "value": "featured"
                                    }
                                ]
                            }]
                        },
                        {
                            "operator": "and",
                            "checks": [
                                {
                                    "operator": "less_than_equals",
                                    "context_accessor": "object.age_restriction",
                                    "value": "18"
                                },
                                {
                                    "operator": "starts_with",
                                    "context_accessor": "subject.permission_level",
                                    "value": "senior"
                                }
                            ]
                        }
                    ]
                },
                {
                    "operator": "or",
                    "checks": [
                        {
                            "operator": "not_ends_with",
                            "context_accessor": "object.category",
                            "value": "restricted"
                        },
                        {
                            "operator": "not",
                            "context_accessor": "subject.access_level",
                            "value": "0"
                        }
                    ]
                }
            ]
        }]
    }]

```

   This JSON structure follows the database schema:
   - `object_attributes`: Maps to `abac_object_additional_attributes` table
   - `subject_attributes`: Maps to `abac_subject_additional_attributes` table
   - `policies`: Maps to `abac_policies` table
   - `chains`: Maps to `abac_chains` table (supports nested chains via `chain_id`)
   - `checks`: Maps to `abac_checks` table

   Note: The `chains` array supports nested structures where a chain can reference another chain using the `chain_id` field. Either `chain_id` or `checks` must be provided in a chain, but not both.

4. Run the seeder:
   ```bash
   php artisan db:seed
   ```

---

## Basic Usage

Here's an example of how to perform access control checks:

```php
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Facades\Abac;

# Using the Facade
$context = new AccessContext(
    method:       $method,
    subject:      (new AbacAttributeLoader())->loadAllSubjectAttributes(get_class('App\Models\User')),
    object:       (new AbacAttributeLoader())->loadAllSubjectAttributes(get_class('App\Models\Post')),
    object_type:  get_class('App\Models\User'),
    subject_type: get_class('App\Models\Post'),
);

# AbacCheck returns boolean
$hasAccess = Abac::can($context);

# Using the helper functions
$hasAccess = abacPolicy()->can($context);

# Cache management helper
abacCache()->warm('posts');  # Warm cache for posts
abacCache()->invalidate();   # Invalidate all cache
abacCache()->clear();        # Clear all cache
```

The `can()` method evaluates the access request and returns a boolean indicating whether access is granted.

The evaluation result is automatically cached using the subject ID, resource, and operation as the cache key.

---

## API Routes

The following API routes are available for managing ABAC-related data. These routes are protected by the `abac`
middleware and can be prefixed using the `ABAC_ROUTE_PREFIX` environment variable:

### Object Attributes

- **Endpoint**: `/{prefix}/object-attributes`
- **Request Body**:
  ```json
  {
    "object_id": "integer",
    "attribute_name": "string",
    "attribute_value": "string"
  }
  ```

### Subject Attributes

- **Endpoint**: `/{prefix}/subject-attributes`
- **Request Body**:
  ```json
  {
    "subject": "string",
    "subject_id": "integer",
    "attribute_name": "string",
    "attribute_value": "string"
  }
  ```

### Policies

- **Endpoint**: `/{prefix}/policies`
- **Request Body**:
  ```json
  {
    "resource": "string",
    "method": "string",
    "chains": "array, optional"
  }
  ```

### Chains

- **Endpoint**: `/{prefix}/policies/{policy}/chains`
- **Request Body**:
  ```json
  {
    "operator": "string",
    "chain_id": "integer|null",
    "policy_id": "integer|null",
    "checks": "array, optional"
  }
  ```

    - **Either chain_id or policy_id must be provided. Providing both will result in an exception.**

### Checks

- **Endpoint**: `/{prefix}/policies/{policy}/chains/{chain}/checks`
- **Request Body**:
  ```json
  {
    "chain_id": "integer",
    "operator": "string",
    "context_accessor": "string",
    "value": "string"
  }
  ```

To register the ABAC routes in your application, create a new service provider:

```bash
php artisan make:provider AbacServiceProvider
```

Then update the provider to register the routes:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use zennit\ABAC\Facades\Abac;

class AbacRoutesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register ABAC routes with custom middleware
        Abac::routes([
            'middleware' => ['api', 'auth'],  // Add your middleware here
            'prefix' => 'abac'                // Optional: customize the route prefix
        ]);
    }
}
```

Register your new service provider in `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\AbacRoutesServiceProvider::class,
],
```

---

## Commands

### Publishing Commands

```bash
# Publish all ABAC files (config, migrations, and env variables)
php artisan abac:publish

# Individual publishing commands
php artisan abac:publish-config    # Publish configuration file only
php artisan abac:publish-migration # Publish migration files only
php artisan abac:publish-env       # Publish environment variables only
```

### Cache Management

```bash
# Warm the entire policy cache
php artisan abac:cache-warm

# Warm cache for specific resource
php artisan abac:cache-warm posts

# Invalidate cache
php artisan abac:cache-invalidate

# Clear cache
php artisan abac:cache-clear
```

### Environment Setup

```bash
# Add required environment variables to .env file
php artisan abac:publish-env

# Available environment variables:
ABAC_CACHE_ENABLED=true
ABAC_CACHE_STORE='database'
ABAC_CACHE_TTL=3600
ABAC_CACHE_PREFIX='abac_'
ABAC_CACHE_WARMING_ENABLED=true
ABAC_CACHE_WARMING_SCHEDULE=100
ABAC_STRICT_VALIDATION=true
ABAC_LOGGING_ENABLED=true
ABAC_LOG_CHANNEL='abac'
ABAC_DETAILED_LOGGING=false
ABAC_PERFORMANCE_LOGGING=true
ABAC_SLOW_EVALUATION_THRESHOLD=100
ABAC_EVENTS_ENABLED=true
ABAC_USER_ATTRIBUTE_SUBJECT_TYPE='users'
ABAC_SUBJECT_METHOD='user'
ABAC_ROUTE_PREFIX='abac' # New environment variable for route prefix
```

### Force Options

All commands support the `--force` option to skip confirmations:

```bash
php artisan abac:publish --force
php artisan abac:publish-config --force
php artisan abac:publish-migration --force
php artisan abac:publish-env --force
```

---

## Configuration

### Environment Variables

```bash
ABAC_CACHE_ENABLED=true                    # Enable/disable the caching system
ABAC_CACHE_STORE=database                  # Cache store to use (database, redis, file, etc.)
ABAC_CACHE_TTL=3600                        # Cache time-to-live in seconds
ABAC_CACHE_PREFIX=abac_                    # Prefix for cache keys
ABAC_CACHE_WARMING_ENABLED=true            # Enable/disable automatic cache warming
ABAC_CACHE_WARMING_SCHEDULE=hourly         # Schedule for cache warming (hourly, daily, etc.)
ABAC_STRICT_VALIDATION=true                # Enable strict validation of attributes and policies
ABAC_LOGGING_ENABLED=true                  # Enable/disable logging
ABAC_LOG_CHANNEL=abac                      # Logging channel for ABAC events
ABAC_DETAILED_LOGGING=false                # Enable detailed logging of evaluations
ABAC_PERFORMANCE_LOGGING_ENABLED=true      # Enable performance metric logging
ABAC_SLOW_EVALUATION_THRESHOLD=100         # Threshold (ms) for slow evaluation warnings
ABAC_EVENTS_ENABLED=true                   # Enable/disable event dispatching
ABAC_OBJECT_ADDITIONAL_ATTRIBUTES=App\Models\User    # Model class for object attributes
ABAC_MIDDLEWARE_OBJECT_METHOD=user         # Method to retrieve object in middleware
```

### Full Configuration Options

```php
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
        'user_attribute_subject_type' => env('ABAC_USER_ATTRIBUTE_SUBJECT_TYPE', App\Models\User::class),
        'user_soft_deletes_column' => 'deleted_at',
    ],
    'seeders' => [
        'user_attribute_path' => env('ABAC_USER_ATTRIBUTE_PATH', 'stubs/abac/user_attributes.json'),
        'resource_attribute_path' => env('ABAC_RESOURCE_ATTRIBUTE_PATH', 'stubs/abac/resource_attributes.json'),
        'permission_path' => env('ABAC_PERMISSION_PATH', 'stubs/abac/permissions.json'),
    ],
    'middleware' => [
        'subject_method' => env('ABAC_MIDDLEWARE_SUBJECT_METHOD', 'user'),
        'excluded_routes' => [
            // Simple wildcard pattern - excludes all methods
            'current-user*',    // Matches current-user, current-user/profile, etc.
            'stats*',           // Matches stats, stats/daily, etc.
            
            // Exclude specific methods for a route pattern
            [
                'path' => 'posts*',  // Wildcard support
                'method' => ['GET', 'POST', 'PUT']  // Array of methods to exclude
            ],
            
            // Exclude all methods for a specific path
            [
                'path' => 'blogs*',
                'method' => '*'
            ],
            
            // Exclude single method
            [
                'path' => 'comments*',
                'method' => 'GET'
            ]
        ],
    ],
];
```

### Permission JSON Structure

The package supports defining permissions through JSON files. The structure should follow:

```json
{
  "permissions": [
    {
      "resource": "string",
      "operation": "string"
    }
  ],
  "policies": [
    {
      "name": "string",
      "permission_id": "integer"
    }
  ],
  "collections": [
    {
      "operator": "string",
      "policy_id": "integer"
    }
  ],
  "conditions": [
    {
      "operator": "string",
      "policy_collection_id": "integer"
    }
  ],
  "attributes": [
    {
      "collection_condition_id": "integer",
      "operator": "string",
      "attribute_name": "string",
      "attribute_value": "string"
    }
  ]
}
```

The JSON file path can be configured in your `.env`:

```bash
ABAC_PERMISSION_PATH=permissions.json
```

### Resource Attributes JSON Structure

The package supports defining resource attributes through JSON files. Create a JSON file with the following structure:

```json
[
  {
    "resource": "posts",
    "attribute_name": "post_owner_id",
    "attribute_value": "5"
  },
  {
    "resource": "posts",
    "attribute_name": "status",
    "attribute_value": "published"
  }
]
```

The resource attributes JSON file path can be configured in your `.env`:

```bash
ABAC_RESOURCE_ATTRIBUTE_PATH=resource-attributes.json
```