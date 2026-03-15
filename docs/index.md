# Laravel ABAC

A flexible Attribute-Based Access Control (ABAC) implementation for Laravel 12+.

## Quick Links

- [Installation & Setup](CONSUMER_SETUP.md)
- [Permission Management API](MANAGING_ABAC.md)
- [Seeding Schema](SEEDING_SCHEMA.md)
- [Architecture Diagrams](ARCHITECTURE_DIAGRAMS.md)
- [Operations Guide](OPERATIONS.md)
- [Benchmarking](BENCHMARKING.md)
- [Security Model](SECURITY_MODEL.md)
- [Upgrade Guide](UPGRADE.md)

## Key Features

- **Permission CRUD API** — Simple facade methods to add, get, update, and remove permissions
- **Flexible Constraints** — Shorthand arrays, explicit arrays, or DSL strings
- **Widening Behavior** — Each grant adds an OR branch for additive access
- **Idempotent Operations** — Duplicate constraints are automatically deduplicated
- **Middleware Protection** — Route protection via `abac` middleware
- **Cache Invalidation** — Automatic cache clearing on policy updates

## Installation

```bash
composer require zennit/abac
php artisan vendor:publish --provider="zennit\ABAC\Providers\AbacServiceProvider"
php artisan migrate
```
