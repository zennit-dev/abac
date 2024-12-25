# ABAC (Attribute-Based Access Control) for Laravel

A flexible and powerful ABAC implementation for Laravel applications.

---

## Table of Contents

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Quick Start](#quick-start)
4. [Basic Usage](#basic-usage)
5. [Commands](#commands)
6. [Configuration](#configuration)
7. [Defining Policies](#defining-policies)
8. [Operators](#operators)
9. [Middleware](#middleware)
10. [Events](#events)
11. [Advanced Usage](#advanced-usage)
12. [Caching](#caching)
13. [Requirements](#requirements)
14. [Contributing](#contributing)
15. [Security](#security)
16. [License](#license)

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
    // ... other providers
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

---

## Basic Usage

Here's an example of how to perform access control checks:

```php
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Facades\Abac;

// Create an access context
$context = new AccessContext(
    subject: $user,
    resource: 'posts',
    operation: 'update',
    resourceIds: [$postId]
);

// Simple check
if (Abac::can($context)) {
    // Allow action
}

// Detailed evaluation
$result = Abac::evaluate($context);
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
ABAC_CACHE_TTL=3600
ABAC_CACHE_WARMING_ENABLED=true
ABAC_CACHE_WARMING_CHUNK_SIZE=100
ABAC_PARALLEL_EVALUATION=false
ABAC_BATCH_SIZE=1000
ABAC_BATCH_CHUNK_SIZE=100
ABAC_STRICT_VALIDATION=true
ABAC_LOGGING_ENABLED=true
ABAC_LOG_CHANNEL=abac
ABAC_DETAILED_LOGGING=false
ABAC_PERFORMANCE_LOGGING=true
ABAC_SLOW_EVALUATION_THRESHOLD=100
ABAC_EVENTS_ENABLED=true
ABAC_ASYNC_EVENTS=false
ABAC_USER_ATTRIBUTE_SUBJECT_TYPE="App\Models\User"
```

### Force Options

All commands support the `--force` option to skip confirmations:

```bash
php artisan abac:publish --force
php artisan abac:publish-config --force
php artisan abac:publish-migration --force
php artisan abac:publish-env --force
```

### Testing

```bash
# Run tests with coverage report
composer test

# Version management
composer version-patch  # Increment patch version
composer version-minor  # Increment minor version
composer version-major  # Increment major version
```

---

## Configuration

### Cache Configuration

```php
// config/abac.php
'cache' => [
    'enabled' => true,
    'ttl' => 3600,
    'tags' => ['abac', 'abac-policies', 'abac-attributes'],
    'prefix' => 'abac:',
    'warming' => [
        'enabled' => true,
        'chunk_size' => 100,
        'schedule' => 'hourly', // Options: hourly, daily, weekly, monthly
    ],
],
```

### Performance Monitoring

```php
'monitoring' => [
    'performance' => [
        'enabled' => true,
        'slow_threshold' => 100, // milliseconds
    ],
    'logging' => [
        'enabled' => true,
        'channel' => 'abac',
        'detailed' => true,
    ],
    'events' => [
        'enabled' => true,
        'async' => false,
    ],
],
```

---

## Defining Policies

```php
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Enums\Operators\ListOperators;

// Create a policy
$policy = Policy::create([
    'name' => 'Edit Own Posts',
    'description' => 'Allow users to edit their own posts',
    'resource' => 'posts',
    'operation' => 'update'
]);

// Add conditions
$policy->conditions()->create([
    'operator' => ListOperators::EQUALS,
    'attributes' => [
        [
            'attribute_name' => 'owner_id',
            'attribute_value' => '$subject.id'
        ]
    ]
]);
```

---

## Operators

Available operators:

- `EQUALS`
- `NOT_EQUALS`
- `GREATER_THAN`
- `LESS_THAN`
- `GREATER_THAN_EQUALS`
- `LESS_THAN_EQUALS`
- `IN`
- `NOT_IN`
- `CONTAINS`
- `NOT_CONTAINS`
- `STARTS_WITH`
- `NOT_STARTS_WITH`
- `ENDS_WITH`
- `NOT_ENDS_WITH`
- `AND`
- `OR`
- `NOT`

---

## Middleware

Protect your routes with ABAC middleware:

```php
// In RouteServiceProvider
Route::middleware(['abac.permissions'])
    ->group(function () {
        // Protected routes
    });
```

---

## Events

The following events are dispatched:

- `CacheWarmed` - When cache warming completes with:
    - Policy count
    - Duration
    - Next warming schedule
    - Resource information

---

## Advanced Usage

### Subject Attributes

```php
use zennit\ABAC\Models\UserAttribute;

// Add attributes to a subject
UserAttribute::create([
    'subject_type' => get_class($subject),
    'subject_id' => $subject->id,
    'attribute_name' => 'role',
    'attribute_value' => 'admin'
]);
```

### Resource Attributes

```php
use zennit\ABAC\Models\ResourceAttribute;

// Add attributes to a resource
ResourceAttribute::create([
    'resource' => 'posts',
    'attribute_name' => 'status',
    'attribute_value' => 'published'
]);
```

### Batch Processing

```php
use zennit\ABAC\Jobs\PolicyCacheJob;

// Warm cache for all policies
PolicyCacheJob::dispatch('warm');

// Warm cache for specific resource
PolicyCacheJob::dispatch('warm', 'posts');

// Invalidate cache
PolicyCacheJob::dispatch('invalidate');
```

---

## Caching

### Cache System

The package includes a comprehensive caching system that caches:

- Policy definitions and conditions
- User attributes
- Resource attributes
- Policy evaluation results
- Attribute collections

### Cache Management

```php
use zennit\ABAC\Jobs\PolicyCacheJob;

// Invalidate all caches
PolicyCacheJob::dispatch('invalidate');

// Invalidate specific resource
PolicyCacheJob::dispatch('invalidate', 'posts');

// Warm all caches
PolicyCacheJob::dispatch('warm');

// Warm specific resource
PolicyCacheJob::dispatch('warm', 'posts');
```

### Automatic Cache Invalidation

The cache automatically invalidates when:

- Policies are modified
- User attributes change
- Resource attributes change
- Permissions are updated

### Cache Events

```php
use zennit\ABAC\Events\CacheWarmed;

Event::listen(CacheWarmed::class, function (CacheWarmed $event) {
    Log::info("Cache warmed: {$event->policiesCount} policies");
    Log::info("Next warming: {$event->getNextWarming()}");
});
```

---

## Requirements

- PHP ^8.2
- Laravel ^11.2

---

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

## Security

If you discover any security-related issues, please email contact@zennit.dev.

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
