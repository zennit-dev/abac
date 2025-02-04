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
   {
     "permissions": [
       {
         "resource": "posts",
         "operation": "view"
       }
     ],
     "policies": [
       {
         "name": "Can view user 5 posts",
         "permission_id": 1
       }
     ],
     "collections": [
       {
         "operator": "and",
         "policy_id": 1
       }
     ],
     "conditions": [
       {
         "operator": "and",
         "policy_collection_id": 1
       }
     ],
     "attributes": [
       {
         "collection_condition_id": 1,
         "operator": "equals",
         "attribute_name": "post_owner_id",
         "attribute_value": "5"
       }
     ]
   }
   ```

4. Run the seeder:
   ```bash
   php artisan db:seed
   ```

---

## Basic Usage

Here's an example of how to perform access control checks:

```php
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\EvaluationResult;
use zennit\ABAC\Facades\Abac;

# Using the Facade
$context = new AccessContext(
    subject: $user,
    resource: 'posts',
    operation: 'update',
    resourceIds: [$postId] # null or [] for all
);

# Check returns boolean
$hasAccess = Abac::can($context);

# Detailed evaluation returns EvaluationResult
$result = Abac::evaluate($context);

# Using the helper functions
$hasAccess = abacPolicy()->can($user, 'posts', 'update', [$postId]);

# Cache management helper
abacCache()->warm('posts');  # Warm cache for posts
abacCache()->invalidate();   # Invalidate all cache
abacCache()->clear();        # Clear all cache
```

The `can()` method evaluates the access request and returns a boolean indicating whether access is granted.

The `evaluate()` method returns an `EvaluationResult` object with detailed information about the evaluation:

```php
class EvaluationResult
{
    public function __construct(
        public readonly bool $granted,
        public readonly array $matchedPolicies = [],
        public readonly array $failedPolicies = [],
        public readonly ?string $reason = null
    ) {}
}
```

The evaluation result is automatically cached using the subject ID, resource, and operation as the cache key.

---

## API Routes

The following API routes are available for managing ABAC-related data. These routes are protected by the `abac`
middleware and can be prefixed using the `ABAC_ROUTE_PREFIX` environment variable:

### User Attributes

- **Endpoint**: `/{prefix}/user-attributes`
- **Request Body**:
  ```json
  {
    "subject_type": "string",
    "subject_id": "string"
  }
  ```

### Resource Attributes

- **Endpoint**: `/{prefix}/resource-attributes`
- **Request Body**:
  ```json
  {
    "resource": "string",
    "attribute_name": "string",
    "attribute_value": "string"
  }
  ```

### Permissions

- **Endpoint**: `/{prefix}/permissions`
- **Request Body**:
  ```json
  {
    "resource": "string",
    "operation": "string",
    "policies": "array"
  }
  ```
- `policies` is required only if query parameter `chain` is set to `true`
- **Optional Query Parameter**: `chain`

### Policies

- **Endpoint**: `/{prefix}/permissions/{permission}/policies`
- **Request Body**:

```json
{
  "name": "string",
  "permission_id": "integer",
  "policy_collection": "array"
}
```

- `policy_collection` is required only if query parameter `chain` is set to `true`
- **Optional Query Parameter**: `chain`

### Policy Collections

- **Endpoint**: `/{prefix}/permissions/{permission}/policies/{policy}/collections`
- **Request Body**:
  ```json
  {
    "operator": "string",
    "policy_id": "integer",
    "collection_conditions": "array"
  }
  ```
- `collection_conditions` is required only if query parameter `chain` is set to `true`
- **Optional Query Parameter**: `chain`

### Collection Conditions

- **Endpoint**: `/{prefix}/permissions/{permission}/policies/{policy}/collections/{collection}/conditions`
- **Request Body**:
  ```json
  {
    "operator": "string",
    "policy_collection_id": "integer",
    "condition_attributes": "array"
  }
  ```
- `condition_attributes` is required only if query parameter `chain` is set to `true`
- **Optional Query Parameter**: `chain`

### Condition Attributes

- **Endpoint**:
  `/{prefix}/permissions/{permission}/policies/{policy}/collections/{collection}/conditions/{condition}/attributes`
- **Request Body**:
  ```json
  {
    "collection_condition_id": "integer",
    "operator": "string",
    "attribute_name": "string",
    "attribute_value": "string"
  }
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
# ABAC Cache Configuration
ABAC_CACHE_ENABLED=true # Enables or disables caching in the package.
ABAC_CACHE_STORE=${CACHE_STORE} # Defines the cache store to use (e.g., database, file, redis).
ABAC_CACHE_TTL=${SESSION_LIFETIME} # Sets the cache time-to-live (TTL) duration in seconds.
ABAC_CACHE_PREFIX=abac_ # Prefix to use for cache keys.
ABAC_CACHE_WARMING_ENABLED=true # Toggles automated cache warming functionality.
ABAC_CACHE_WARMING_SCHEDULE=hourly # Specifies the cache warming schedule (e.g., hourly, daily).

# ABAC Validation Configuration
ABAC_STRICT_VALIDATION=true # Enforces strict validation of attributes and access configurations.

# ABAC Logging Configuration
ABAC_LOGGING_ENABLED=true # Enables or disables logging of ABAC activities.
ABAC_LOG_CHANNEL=${LOG_CHANNEL} # Specifies the logging channel dedicated to ABAC logs.
ABAC_DETAILED_LOGGING=false # Enables detailed logging of each access evaluation.
ABAC_PERFORMANCE_LOGGING_ENABLED=true # Toggles logging of access evaluation performance metrics.
ABAC_SLOW_EVALUATION_THRESHOLD=100 # Threshold (in milliseconds) for slow evaluation logging.

# ABAC Events Configuration
ABAC_EVENTS_ENABLED=true # Enables event-based notifications for ABAC operations.

# ABAC Model Configuration
ABAC_USER_ATTRIBUTE_SUBJECT_TYPE=App\Models\User # Default subject type for user attributes in the database (e.g., App\\Models\\User).
ABAC_MIDDLEWARE_SUBJECT_METHOD=user # Default method for resolving middleware subjects (e.g., user).

# ABAC Route Configuration
ABAC_ROUTE_PREFIX=abac # Sets the prefix for the package's API routes.

# ABAC File Configuration
ABAC_USER_ATTRIBUTE_PATH=stubs/abac/user_attributes.json # Path to the user attributes JSON file.
ABAC_RESOURCE_ATTRIBUTE_PATH=stubs/abac/resource_attributes.json # Path to the resource attributes JSON file.
ABAC_PERMISSION_PATH=stubs/abac/permissions.json # Path to the permissions JSON file.
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
        'user_attribute_subject_type' => env('ABAC_USER_ATTRIBUTE_SUBJECT_TYPE', 'users'),
    ],
    'middleware' => [
        'subject_method' => env('ABAC_SUBJECT_METHOD', 'user'),
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
    'routes' => [
        'prefix' => env('ABAC_ROUTE_PREFIX', 'abac'),
        'middleware' => ['auth:sanctum', 'abac'], or 'auth:sanctum,abac'
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

### User Attributes JSON Structure

The package supports defining user attributes through JSON files. Create a JSON file with the following structure:

```json
[
  {
    "subject_id": 1,
    "attribute_name": "role",
    "attribute_value": "admin"
  },
  {
    "subject_id": 5,
    "attribute_name": "department",
    "attribute_value": "engineering"
  }
]
```

The user attributes JSON file path can be configured in your `.env`:

```bash
ABAC_USER_ATTRIBUTE_PATH=user-attributes.json
```

Note: The `subject_type` is automatically set from your configuration `ABAC_USER_ATTRIBUTE_SUBJECT_TYPE`.

## Database Schema

The package creates the following tables:

### Permissions

- `id` - Primary key
- `resource` - Resource identifier
- `operation` - Operation name
- Unique constraint on `[resource, operation]`

### Policies

- `id` - Primary key
- `name` - Policy name
- `permission_id` - Foreign key to permissions table

### Policy Collections

- `id` - Primary key
- `operator` - Logical operator (AND, OR)
- `policy_id` - Foreign key to policies table

### Policy Conditions

- `id` - Primary key
- `operator` - Logical operator
- `policy_collection_id` - Foreign key to policy_collections table

### Policy Condition Attributes

- `id` - Primary key
- `collection_condition_id` - Foreign key to condition_attributes table
- `operator` - Comparison operator
- `attribute_name` - Name of the attribute to compare
- `attribute_value` - Value to compare against

### Resource Attributes

- `id` - Primary key
- `resource` - Resource identifier
- `attribute_name` - Name of the attribute
- `attribute_value` - Value of the attribute
- Index on `[resource, attribute_name]`

### User Attributes

- `id` - Primary key
- `subject_type` - Morphable type (default: App\Models\User)
- `subject_id` - Subject ID
- `attribute_name` - Name of the attribute
- `attribute_value` - Value of the attribute
- Unique constraint on `[subject_type, subject_id, attribute_name]`

## Models

### Permission

```php
use zennit\ABAC\Models\Permission;

$permission = Permission::create([
    'resource' => 'posts',
    'operation' => 'update'
]);

# Relationships
$permission->policies(); # HasMany Policy
```

### Policy

```php
use zennit\ABAC\Models\Policy;

$policy = Policy::create([
    'name' => 'Edit Own Posts',
    'permission_id' => $permissionId
]);

# Relationships
$policy->permission();  # BelongsTo Permission
$policy->collections(); # HasMany PolicyCollection
```

### PolicyCollection

```php
use zennit\ABAC\Models\PolicyCollection;

$collection = PolicyCollection::create([
    'operator' => 'AND',
    'policy_id' => $policyId
]);

# Relationships
$collection->policy();     # BelongsTo Policy
$collection->conditions(); # HasMany CollectionCondition
```

### PolicyCondition

```php
use zennit\ABAC\Models\CollectionCondition;

$condition = CollectionCondition::create([
    'operator' => 'AND',
    'policy_collection_id' => $collectionId
]);

# Relationships
$condition->collection(); # BelongsTo PolicyCollection
$condition->attributes(); # HasMany ConditionAttribute
```

### PolicyConditionAttribute

```php
use zennit\ABAC\Models\ConditionAttribute;

$attribute = ConditionAttribute::create([
    'collection_condition_id' => $conditionId,
    'attribute_name' => 'owner_id',
    'attribute_value' => '$subject.id',
    'operator' => 'EQUALS'
]);

# Relationships
$attribute->condition(); # BelongsTo CollectionCondition
```

### ResourceAttribute

```php
use zennit\ABAC\Models\ResourceAttribute;

$attribute = ResourceAttribute::create([
    'resource' => 'posts',
    'attribute_name' => 'status',
    'attribute_value' => 'published'
]);
```

### UserAttribute

```php
use zennit\ABAC\Models\UserAttribute;

$attribute = UserAttribute::create([
    'subject_type' => 'App\\Models\\User',
    'subject_id' => $userId,
    'attribute_name' => 'role',
    'attribute_value' => 'admin'
]);

# Relationships
$attribute->subject(); # MorphTo
```

---

## Operators

Available operators:

### Arithmetic Operators

- `EQUALS`
- `NOT_EQUALS`
- `GREATER_THAN`
- `LESS_THAN`
- `GREATER_THAN_EQUALS`
- `LESS_THAN_EQUALS`

### String Operators

- `CONTAINS`
- `NOT_CONTAINS`
- `STARTS_WITH`
- `NOT_STARTS_WITH`
- `ENDS_WITH`
- `NOT_ENDS_WITH`

### Logical Operators

- `AND`
- `OR`
- `NOT`

## Context Value Resolution

The ABAC system supports dynamic context value resolution in attribute comparisons. You can use special syntax to
reference values from the access context:

### Subject Values

```php
'$subject.id'              // Gets user ID
'$subject.profile.name'    // Gets nested user property
'$subject.department'      // Gets user department
```

### Resource Values

```php
'$resource'                // Gets resource name
'$resource.file.name'      // Gets file name from resource context
'$resource.metadata.type'  // Gets resource metadata
```

### Operation Value

```php
'$operation'               // Gets current operation
```

### Custom Context Values

```php
'$context.custom_value'    // Gets value from context array
```

### Example Usage

```php
// Define a policy condition
$condition = [
    'operator' => 'equals',
    'attribute_name' => 'owner_id',
    'attribute_value' => '$subject.id'  // Will be replaced with actual user ID
];

// Check if file type matches
$condition = [
    'operator' => 'contains',
    'attribute_name' => '$resource.file.type',
    'attribute_value' => 'image'
];

// Access context with custom values
$context = new AccessContext(
    resource: 'files',
    operation: 'view',
    subject: $user,
    context: [
        'resource' => [
            'file' => [
                'name' => 'document.pdf',
                'type' => 'document'
            ]
        ],
        'custom_value' => 'test'
    ]
);
```

All operators (Arithmetic, String, and Logical) support context value resolution through the `HandlesContextValues`
trait.


## License

This package is open-sourced software licensed under the MIT license. See [LICENSE.md](LICENSE.md) for more details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

Before contributing, please read our [Contributing Guide](CONTRIBUTING.md) which covers:
- Code of conduct and etiquette
- Feature request guidelines
- Pull request process
- Coding standards

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Format code: `./vendor/bin/pint`

### Coding Standards

This package follows Laravel's coding standards using Laravel Pint. To format your code:

```bash
./vendor/bin/pint
```

To check the code without making changes:

```bash
./vendor/bin/pint --test
```

### Security

If you discover any security-related issues, please email support@zennit.dev instead of using the issue tracker.
