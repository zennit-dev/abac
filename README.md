# ABAC (Attribute-Based Access Control) for Laravel

A flexible and powerful ABAC implementation for Laravel applications.

---

## Table of Contents

1. [Introduction](#introduction)
2. [Prerequisites](#prerequisites)
3. [Installation](#installation)
4. [Quick Start](#quick-start)
5. [Basic Usage](#basic-usage)
6. [API Routes](#api-routes)
7. [Commands](#commands)
8. [Configuration](#configuration)
9. [Database Schema](#database-schema)
10. [Performance Monitoring](#performance-monitoring)
11. [License](#license)
12. [Contributing](#contributing)
13. [Reporting Issues & Questions](#reporting-issues--questions)

---

## Introduction

ABAC provides fine-grained access control by evaluating attributes of users, resources, and the context of a request.

This package integrates seamlessly with Laravel to offer powerful access control mechanisms.

---

## Prerequisites

- Laravel 12.0 or higher
- LaravelSanctum 4.0 or higher
- PHP 8.3 or higher

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

### Migrate

   ```bash
   php artisan migrate
   ```

### Setup initial data using the provided JSON files. You can use the following example JSON structure to seed your database

```json
{
  "object_attributes": [
    {
      "object_id": 1,
      "attribute_name": "posts_id",
      "attribute_value": "1"
    },
    {
      "object_id": 2,
      "attribute_name": "user_id",
      "attribute_value": "1"
    }
  ],
  "subject_attributes": [
    {
      "subject": "App\\Models\\User",
      "subject_id": 1,
      "attribute_name": "role",
      "attribute_value": "editor"
    }
  ],
  "policies": [
    {
      "resource": "App\\Models\\Post",
      "method": "view",
      "chains": [
        {
          "operator": "or",
          "chains": [
            {
              "operator": "or",
              "checks": [
                {
                  "operator": "equals",
                  "context_accessor": "object.posts.id",
                  "value": "1"
                },
                {
                  "operator": "equals",
                  "context_accessor": "object.user.id",
                  "value": "1"
                }
              ]
            }
          ]
        },
        {
          "operator": "or",
          "checks": [
            {
              "operator": "equals",
              "context_accessor": "object.posts.id",
              "value": "1"
            },
            {
              "operator": "equals",
              "context_accessor": "object.user.id",
              "value": "1"
            }
          ]
        }
      ]
    }
  ]
}
```

This example creates a policy that determines who can view posts. It allows viewing when either:

- The post ID equals 1, OR
- The user ID equals 1

This JSON structure follows the database schema:

- `object_attributes`: Maps to `abac_object_additional_attributes` table
- `subject_attributes`: Maps to `abac_subject_additional_attributes` table
- `policies`: Maps to `abac_policies` table
- `chains`: Maps to `abac_chains` table (supports nested chains via `chain_id`)
- `checks`: Maps to `abac_checks` table

Note: The `chains` array supports nested structures where a chain can reference another chain using the `chain_id`
field. Either `chain_id` or `checks` must be provided in a chain, but not both.

### Before running the seeder

- Make sure you have published the [configuration file](config/abac.php)  and set the correct paths for the JSON files
  in your environment.

```bash
php artisan abac:publish
```

This will add the required environment variables to your `.env` file and create the configuration file at
`config/abac.php`.

- Also include the [DatabaseSeeder](database/seeders/DatabaseSeeder.php) into your `DatabaseSeeder` class

```php
use zennit\ABAC\Database\Seeders\DatabaseSeeder as ABACDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Other seeders...
        $this->call(ABACDatabaseSeeder::class, []);
    }
}
```

### Run the seeder command

```bash
php artisan db:seed --class=AbacSeeder
```

### After seeding the database

The final step to complete the setup is to add the [trait](src/Traits/IntegratesAbacAdditionalAttributes.php) to all the
classes you have defined in path_patterns in the configuration file.

- Check the [Configuration](#configuration) section for more details.

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

The `evaluate()` method can be used to get detailed information about the evaluation process, look
at [AccessResult](/src/DTO/AccessResult.php) for more details.

The evaluation result is automatically cached using the subject ID, resource, and operation as the cache key.

### Available Operators

All operators are defined in the `src/Enums/Operators` directory

#### [Logical Operators](src/Enums/Operators/LogicalOperators.php)

- `and`
- `or`

#### [Arithmetic Operators](src/Enums/Operators/ArithmeticOperators.php)

- `equals`
- `not_equals`
- `greater_than`
- `less_than`
- `greater_than_equals`
- `less_than_equals`

#### [String Operators](src/Enums/Operators/StringOperators.php)

- `contains`
- `not_contains`
- `starts_with`
- `ends_with`
- `not_starts_with`
- `not_ends_with`

**Note: Make sure when creating new objects in the database, you make use of these operators, not doing so is going to
make the conditions invalid. If you want us to support other operators make a PR or raise an issue**

---

## Middleware

The ABAC package uses the [EnsureAccess Middleware](src/Http/Middleware/EnsureAccess.php) to check whether the
requesting user has the necessary permissions to access a specific resource and method.

### How It Works

The middleware is automatically registered when you add the service provider to your `bootstrap/providers.php` file. It
evaluates access requests based on the following parameters:

- **Resource**: The resource being accessed (e.g., `App\Models\Post`, `App\Models\User`, etc.).
- **Method**: The HTTP method of the request (e.g., `GET`, `POST`, etc.).
- **Subject**: The user making the request (e.g., `$request->user()`).

For more details, refer to the [AccessContext](src/DTO/AccessContext.php) class.

### Accessing the Evaluation Result

After processing the request, the middleware adds the evaluation result to the request object. You can access it using
`$request->abac()`.

### Usage Example

To use the middleware, simply apply it to your routes. For example:

```php
Route::apiResource('users', UserController::class)->middleware('abac');
```

### Important Notes

- Ensure that the `abac` middleware is applied to the routes you want to protect.
- The middleware evaluates access dynamically based on the defined policies and attributes.

---

## API Routes

The following API routes are available for managing ABAC-related data. These routes must be protected by the `abac`
middleware (look at [Configuration](#configuration) for more details) and can be prefixed using the `ABAC_ROUTE_PREFIX`
environment variable:

### For Update and Create Operations

When creating or updating ABAC resources, you have flexibility in how you structure your requests, as long as they
comply with the validation rules defined in the [FormRequests](src/Http/Requests) classes. These rules ensure data
integrity and proper relationships between policies, chains, and checks.

You can create complex policy structures in a single request or build them incrementally through separate API calls. The
validation system will ensure that your data maintains proper hierarchical relationships and contains all required
fields.

For detailed validation rules and request structure examples, refer to the FormRequest classes:

- [AbacPolicyRequest](src/Http/Requests/AbacPolicyRequest.php)
- [AbacChainRequest](src/Http/Requests/AbacChainRequest.php)
- [AbacCheckRequest](src/Http/Requests/AbacCheckRequest.php)
- [AbacObjectAttributeRequest](src/Http/Requests/AbacObjectAdditionalAttributesRequest.php)
- [AbacSubjectAttributeRequest](src/Http/Requests/AbacSubjectAdditionalAttributeRequest.php)

### Pagination

All index responses are paginated and support the following query parameters:

- `page`: The page number to retrieve (default: 1)
- `per_page`: The number of items per page (default: 10)

The pagination response format includes both the data items and pagination metadata:

```json
{
  "items": [],
  "pagination": {
    "firstPage": 1,
    "currentPage": 1,
    "lastPage": 10,
    "firstPageUrl": "localhost:8000/{prefix}/object-attributes?page=1",
    "lastPageUrl": "localhost:8000/{prefix}/object-attributes?page=10",
    "perPage": 10,
    "nextPageUrl": "localhost:8000/{prefix}/object-attributes?page=2",
    "prevPageUrl": null,
    "total": 100,
    "hasMorePages": true
  }
}
```

### Object Attributes

- **Get Object Attributes**
    - **Endpoint**: `/{prefix}/object-attributes`
    - **Method**: `GET`
    - **Description**: Retrieve a list of object attributes.
    - **Request Body**: None
    - **Response**:
      ```json
          {
            "object_id": 1,
            "attribute_name": "name_1",
            "attribute_value": "val_1"
          }
      ```

- **Delete Object Attribute**
    - **Endpoint**: `/{prefix}/object-attributes/{id}`
    - **Method**: `DELETE`
    - **Description**: Delete a specific object attribute by ID.
    - **Request Body**: None
    - **Response**:
      ```json
      {
        "message": "Object Attribute deleted successfully."
      }
      ```

### Subject Attributes

- **Get Subject Attributes**
    - **Endpoint**: `/{prefix}/subject-attributes`
    - **Method**: `GET`
    - **Description**: Retrieve a list of subject attributes.
    - **Request Body**: None
- **Response**:
  ```json
      {
        "subject": "App\\Models\\User",
        "subject_id": 1,
        "attribute_name": "role",
        "attribute_value": "admin"
      }
  ```

- **Delete Subject Attribute**
    - **Endpoint**: `/{prefix}/subject-attributes/{id}`
    - **Method**: `DELETE`
    - **Description**: Delete a specific subject attribute by ID.
    - **Request Body**: None
    - **Response**:
      ```json
      {
        "message": "Subject attribute deleted successfully."
      }
      ```

### Policies

- **Get Policies**
    - **Endpoint**: `/{prefix}/policies`
    - **Method**: `GET`
    - **Description**: Retrieve a list of policies.
    - **Request Body**: None
    - **Response**:
      ```json
      {
        "id": 1,
        "resource": "posts",
        "method": "view",
        "chains": []
      }
      ```

- **Delete Policy**
    - **Endpoint**: `/{prefix}/policies/{id}`
    - **Method**: `DELETE`
    - **Description**: Delete a specific policy by ID.
    - **Request Body**: None
    - **Response**:
      ```json
      {
        "message": "Policy deleted successfully."
      }
      ```

### Chains

- **Get Chains**
    - **Endpoint**: `/{prefix}/policies/{policy}/chains`
    - **Method**: `GET`
    - **Description**: Retrieve a list of chains for a given policy.
    - **Request Body**: None
    - **Response**:
      ```json
      {
        "id": 1,
        "operator": "and",
        "checks": []
      }
      ```

- **Delete Chain**
    - **Endpoint**: `/{prefix}/policies/{policy}/chains/{id}`
    - **Method**: `DELETE`
    - **Description**: Delete a specific chain from a policy.
    - **Request Body**: None
    - **Response**:
      ```json
      {
        "message": "Chain deleted successfully."
      }
      ```

### Checks

- **Get Checks**
    - **Endpoint**: `/{prefix}/policies/{policy}/chains/{chain}/checks`
    - **Method**: `GET`
    - **Description**: Retrieve a list of checks for a given chain.
    - **Request Body**: None
    - **Response**:
      ```json
      {
        "id": 1,
        "operator": "greater_than",
        "context_accessor": "object.view_count",
        "value": "1000"
      }
      ```

- **Delete Check**
    - **Endpoint**: `/{prefix}/policies/{policy}/chains/{chain}/checks/{id}`
    - **Method**: `DELETE`
    - **Description**: Delete a specific check from a chain.
    - **Request Body**: None
    - **Response**:
      ```json
      {
        "message": "Check deleted successfully."
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
        
        // Load ABAC macros so you have access to $request->abac()
        Abac::macros();
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
# Publish all ABAC files (config and env variables)
php artisan abac:publish

# Individual publishing commands
php artisan abac:publish-config    # Publish configuration file only
php artisan abac:publish-env       # Publish environment variables only
```

### Force Options

All commands support the `--force` option to skip confirmations:

```bash
php artisan abac:publish --force
php artisan abac:publish-config --force
php artisan abac:publish-env --force
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
    ],
    'database' => [
        'object_additional_attributes' => env('ABAC_OBJECT_ADDITIONAL_ATTRIBUTES', 'App\Models\User'),
        'soft_deletes_column' => 'deleted_at',
    ],
    'seeders' => [
        'object_attribute_path' => 'stubs/abac/object_attribute_path.json',
        'subject_attribute_path' => 'stubs/abac/subject_attribute_path.json',
        'policy_file_path' => 'stubs/abac/abac_policy_file_path.json',
    ],
    'middleware' => [
        'object_method' => env('ABAC_MIDDLEWARE_OBJECT_METHOD', 'user'),
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
        // key value pairs for matching the URI to its associated method, required for the middleware to work
        'path_patterns' => [
          'users/(/[^/]+)?' => 'App\Models\User',
          'users/(/[^/]+)/posts/(/[^/]+)?' => 'App\Models\Post',
        ] 
    ],
];
```

## Database Schema

![Database Schema](/resources/abac_db_model.png)

## Performance Monitoring

The ABAC package includes built-in performance monitoring capabilities:

### Caching System

- **Automatic Result Caching**: Access control evaluations are automatically cached to improve performance
- **Configurable Cache Store**: Supports multiple cache backends (database, Redis, file, etc.) via the
  `ABAC_CACHE_STORE` setting
- **Custom Cache Prefix**: All cache keys use the configured prefix (`ABAC_CACHE_PREFIX`) for easy identification
- **Cache Registry**: The system maintains a registry of all cache keys for efficient invalidation
- **Serialization Strategy**: Evaluation results are serialized efficiently, storing SQL queries and bindings rather
  than full objects

### Cache Management

- All evaluation results are cached by default to improve performance
- Cache storage uses the store defined in your configuration (`ABAC_CACHE_STORE`)
- Cache warm-up can be scheduled with `ABAC_CACHE_WARMING_SCHEDULE` option
- If caching is disabled (`ABAC_CACHE_ENABLED=false`), evaluations will be performed for each request

### Performance Metrics

- The package monitors the performance of all access control evaluations
- Any evaluation that takes longer than the configured threshold (`ABAC_SLOW_EVALUATION_THRESHOLD`, default 100ms) will
  log a warning
- Performance metrics can be viewed in your configured log channel (`ABAC_LOG_CHANNEL`)

These monitoring tools help identify and resolve performance bottlenecks in your access control policies.

---

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please read the [CONTRIBUTING](CONTRIBUTING.md) file for details on how to contribute to this
project.

---

## Reporting Issues & Questions

If you encounter any issues, have questions, or need assistance with the ABAC package, please feel free to open an issue
on our GitHub repository:

[https://github.com/zennit-dev/abac/issues](https://github.com/zennit-dev/abac/issues)

Our team monitors the issues board regularly and will respond as soon as possible. When reporting issues, please
include:

- Laravel and PHP versions
- Package version
- Steps to reproduce the issue
- Expected and actual behavior
- Any relevant error messages or logs

This helps us address your concerns more efficiently.
