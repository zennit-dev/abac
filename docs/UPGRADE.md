# Upgrade Guide

## 6.x -> 6.8+ (Primary Key and Policy Hardening)

### What changed

- ABAC now supports configurable primary key and fallback key:
  - `ABAC_PRIMARY_KEY`
  - `ABAC_FALLBACK_PRIMARY_KEY`
- Middleware supports route-model-binding-first resolution.
- Policy fallback behavior is explicit via `ABAC_DEFAULT_POLICY_BEHAVIOR`.
- Unmatched route behavior is explicit via `ABAC_ALLOW_IF_UNMATCHED_ROUTE`.
- Additional attributes `_id` columns now support string/UUID values.

### Action required for existing installs using UUID/custom keys

1. Publish/update config and env keys.
2. Add migration(s) in your app to alter these columns to string (if needed):
   - `abac_actor_additional_attributes._id`
   - `abac_resource_additional_attributes._id`
3. Add/verify indexes for frequently queried key columns.
4. Apply the hardened production profile from [Operations Guide](OPERATIONS.md).

### Suggested SQL migration pattern (Laravel)

```php
Schema::table('abac_actor_additional_attributes', function (Blueprint $table) {
    $table->string('_id')->change();
});

Schema::table('abac_resource_additional_attributes', function (Blueprint $table) {
    $table->string('_id')->nullable()->change();
});
```
