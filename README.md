# ABAC (Attribute-Based Access Control) for Laravel

A flexible ABAC implementation for Laravel 12+ with a developer-friendly permission management API.

[![PHP Version](https://img.shields.io/packagist/php-v/zennit/abac)](https://packagist.org/packages/zennit/abac)
[![Laravel Version](https://img.shields.io/packagist/laravel-v/zennit/abac)](https://packagist.org/packages/zennit/abac)
[![License](https://img.shields.io/packagist/l/zennit/abac)](LICENSE.md)

---

## Installation

```bash
composer require zennit/abac
```

Publish config and migrate:

```bash
php artisan vendor:publish --provider="zennit\ABAC\Providers\AbacServiceProvider"
php artisan migrate
```

---

## Quick Start

### 1. Configure Resource Patterns

```php
// config/abac.php
'middleware' => [
    'resource_patterns' => [
        'posts/([^/]+)' => App\Models\Post::class,
    ],
    'actor_method' => 'user', // method to get actor from request
],
```

### 2. Add Permissions

```php
use zennit\ABAC\Facades\Abac;

// Shorthand - keys default to actor.*
$grant = Abac::addPermission('read', App\Models\Post::class, [
    'role' => 'editor',
    'resource.owner_id' => 123,
]);

// Explicit constraints
$grant = Abac::addPermission('read', App\Models\Post::class, [
    ['key' => 'actor.role', 'operator' => 'equals', 'value' => 'admin'],
    ['key' => 'resource.owner_id', 'operator' => 'equals', 'value' => '123'],
]);

// DSL string
$grant = Abac::addPermission('read', App\Models\Post::class, 
    'actor.role=admin and resource.owner_id=123'
);
```

### 3. Manage Permissions

```php
// Get all grants
$grants = Abac::getPermissions('read', App\Models\Post::class);

// Get single grant
$grant = Abac::getPermission($grantId);

// Update grant
$updated = Abac::updatePermission($grantId, ['role' => 'superadmin']);

// Remove single grant
Abac::removePermission($grantId);

// Remove all grants for method/resource
Abac::removePermissions('read', App\Models\Post::class);
```

### 4. Protect Routes

```php
// routes/web.php
Route::middleware(['web', 'abac'])->group(function () {
    Route::get('/posts/{post}', fn (Post $post) => $post);
});
```

---

## Constraint Keys

| Prefix          | Description                                        |
|-----------------|----------------------------------------------------|
| `actor.*`       | Requester attributes (user role, tenant, etc.)     |
| `resource.*`    | Resource being accessed (post owner, status, etc.) |
| `environment.*` | Request context (ip, time, etc.) — reserved        |

Shorthand keys without prefix default to `actor.*`:
```php
['role' => 'admin']  // becomes actor.role
```

---

## Supported Operators

| Type       | Operators                              |
|------------|----------------------------------------|
| Arithmetic | `=`, `!=`, `>`, `<`, `>=`, `<=`        |
| String     | `contains`, `starts_with`, `ends_with` |

DSL aliases: `=`, `!=`, `>`, `<`, `>=`, `<=`, `~`, `!~`, `^=`, `!^`, `$=`, `!$`

---

## Behavior

- **Widening**: `addPermission()` adds new OR branches — each grant is additive.
- **Idempotent**: Duplicate constraints return the existing grant.
- **Transactional**: Updates and deletes are atomic.

---

## Documentation

- [Consumer Setup](docs/CONSUMER_SETUP.md) — Installation & configuration
- [Managing Permissions](docs/MANAGING_ABAC.md) — Permission CRUD API
- [Seeding Schema](docs/SEEDING_SCHEMA.md) — JSON schema for seeders
- [Architecture Diagrams](docs/ARCHITECTURE_DIAGRAMS.md) — ER diagrams

---

## License

MIT License — see [LICENSE.md](LICENSE.md)
