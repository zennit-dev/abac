# Seeding Permissions in Consumer Apps

Package-level seeders are intentionally not used. Seed ABAC permissions from your consuming application's own seeders so policy lifecycle stays with your domain code.

## Recommended approach

Create a dedicated app seeder (for example `Database\\Seeders\\AbacPermissionSeeder`) and use the facade API.

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use zennit\ABAC\Facades\Abac;

class AbacPermissionSeeder extends Seeder
{
    public function run(): void
    {
        Abac::addPermission('read', App\Models\Post::class, [
            'role' => 'editor',
            'resource.owner_id' => '123',
        ]);

        Abac::addPermission('update', App\Models\Post::class, [
            'actor.role' => 'admin',
        ]);
    }
}
```

Register it in your app `DatabaseSeeder`:

```php
$this->call([
    AbacPermissionSeeder::class,
]);
```

## Constraint rules

- Use `actor.*`, `resource.*`, or `environment.*` keys.
- Shorthand keys default to `actor.*` (`'role' => 'editor'` means `actor.role`).
- Methods should match supported policy methods (`read`, `create`, `update`, `delete`).

## Why this is preferred

- Keeps permissions versioned alongside your app modules.
- Makes environment-specific seeding straightforward.
- Avoids package-owned seed state and cross-project coupling.
