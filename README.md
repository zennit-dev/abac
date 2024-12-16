# Laravel ABAC (Attribute-Based Access Control)

A flexible and powerful Attribute-Based Access Control system for Laravel applications.

## Installation

You can install the package via composer:

```bash
composer require zennit/abac
```

## Configuration

Publish the configuration and migrations:

```bash
php artisan abac:publish
```

Run the migrations:

```bash
php artisan migrate
```

## Basic Usage

```php
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Facades\Abac;

// Create an access context
$context = new AccessContext(
    subject: $organization, // Any object with an ID
    resource: 'posts',
    operation: PermissionOperations::UPDATE->value,
    resourceIds: [$postId]
);

// Check access
if (Abac::can($context)) {
    // Allow action
}

// Get detailed evaluation result
$result = Abac::evaluate($context);
```

## Defining Policies

```php
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Enums\PolicyOperators;

// Create a policy
$policy = Policy::create([
    'name' => 'Edit Own Posts',
    'description' => 'Allow users to edit their own posts',
    'resource' => 'posts',
    'operation' => PermissionOperations::UPDATE->value
]);

// Add conditions
$policy->conditions()->create([
    'operator' => PolicyOperators::EQUALS,
    'attributes' => [
        [
            'attribute_name' => 'owner_id',
            'attribute_value' => '$subject.id'
        ]
    ]
]);
```

## Available Operators

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

## Subject Attributes

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

## Resource Attributes

```php
use zennit\ABAC\Models\ResourceAttribute;

// Add attributes to a resource
ResourceAttribute::create([
    'resource' => 'posts',
    'attribute_name' => 'status',
    'attribute_value' => 'published'
]);
```

## Performance Optimization

### Caching

Configure caching in `config/abac.php`:

```php
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // 1 hour
    'tags' => ['abac', 'abac-policies', 'abac-attributes'],
    'prefix' => 'abac:',
],
```

### Batch Processing

```php
use zennit\ABAC\Jobs\BatchEvaluateAccessJob;

// Evaluate multiple contexts at once
BatchEvaluateAccessJob::dispatch($contexts, parallel: true);
```

### Cache Warming

```php
use zennit\ABAC\Jobs\WarmPolicyCacheJob;

// Warm cache for all policies
WarmPolicyCacheJob::dispatch();

// Warm cache for specific resource
WarmPolicyCacheJob::dispatch('posts');
```

## Events

- `AccessEvaluated`
- `PolicyCreated`
- `PolicyUpdated`
- `PolicyDeleted`
- `CacheWarmed`

## Logging

Configure logging in `config/abac.php`:

```php
'logging' => [
    'enabled' => true,
    'channel' => 'abac',
    'detailed' => true,
],
```

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@example.com instead of using the issue tracker.

## Credits

- [Arbi Kullakshi](https://github.com/somethim)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
