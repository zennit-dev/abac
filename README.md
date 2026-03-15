# ABAC (Attribute-Based Access Control) for Laravel

A flexible ABAC implementation for Laravel 12+ with a developer-friendly permission management API.

[![PHP Version](https://img.shields.io/packagist/php-v/zennit/abac?cacheSeconds=300)](https://packagist.org/packages/zennit/abac)
[![License](https://img.shields.io/packagist/l/zennit/abac?cacheSeconds=300)](LICENSE.md)
[![Packagist Version](https://img.shields.io/packagist/v/zennit/abac?cacheSeconds=300)](https://packagist.org/packages/zennit/abac)

---

## Installation

```bash
composer require zennit/abac
```

Publish config and run migrations:

```bash
php artisan vendor:publish --provider="zennit\ABAC\Providers\AbacServiceProvider"
php artisan migrate
```

---

## Quick Start

1. Add the middleware to protected routes:

```php
Route::middleware(['web', 'abac'])->group(function () {
    Route::get('/posts/{post}', fn (Post $post) => $post);
});
```
2. Add a permission:

```php
use zennit\ABAC\Facades\Abac;

Abac::addPermission('read', App\Models\Post::class, [
    'role' => 'editor',
    'resource.owner_id' => 123,
]);
```

3. Request is allowed when actor/resource attributes satisfy the grant constraints.

---

## Documentation

Full docs: [https://zennit-dev.github.io/abac/](https://zennit-dev.github.io/abac/)

Local docs index: `docs/index.md`

---

## License

MIT License — see [LICENSE.md](LICENSE.md)
