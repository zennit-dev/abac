# Operations Guide

## Recommended cache stores in production

ABAC evaluation is read-heavy and benefits from a shared cache across app instances.

- Redis: best for horizontally scaled apps; low latency and shared state.
- Database cache: acceptable default when Redis is unavailable; simpler operationally.
- File/array stores: only for local development and tests.

### Redis vs database tradeoffs

- Redis pros: lower latency, better throughput, natural fit for frequent invalidation.
- Redis cons: extra infrastructure and monitoring.
- Database pros: no extra service, easier onboarding in smaller deployments.
- Database cons: higher latency and additional load on primary DB.

## Rollout guidance for unmatched routes

Use `ABAC_ALLOW_IF_UNMATCHED_ROUTE` as a temporary rollout lever.

1. Start with `ABAC_ALLOW_IF_UNMATCHED_ROUTE=true` during initial route mapping.
2. Add `resource_patterns` coverage for all protected routes and monitor policy misses.
3. Validate deny/allow behavior in staging with realistic traffic.
4. Set `ABAC_ALLOW_IF_UNMATCHED_ROUTE=false` before production hardening signoff.

## Enterprise-safe defaults

For strict environments, use:

- `ABAC_DEFAULT_POLICY_BEHAVIOR=deny`
- `ABAC_ALLOW_IF_UNMATCHED_ROUTE=false`
- `ABAC_CACHE_INCLUDE_CONTEXT=true`
- `ABAC_CACHE_STORE=redis` (or `database` when Redis is unavailable)

## Bulk writes and cache invalidation

During large seed/import operations, frequent ABAC model writes can trigger repeated cache flushes.

- Default behavior: `ABAC_CACHE_FLUSH_ON_WRITE=true` (flush after create/update/delete on ABAC models).
- Bulk mode: set `ABAC_CACHE_FLUSH_ON_WRITE=false` for the batch window to avoid flush storms.
- After bulk updates: re-enable write flushes and perform a single cache flush through your app service container.

## Optional metrics hooks

For custom telemetry (evaluation count/latency/cache hit ratio), bind your own
`zennit\ABAC\Contracts\MetricsCollector` implementation.
