# TODO - Production Readiness (`abac`)

## P0 - Must Fix Before Calling It Production

- [x] Add a committed automated test suite (`tests/`) and `phpunit.xml`.
- [x] Add CI (GitHub Actions): run Pint, PHPUnit, and static analysis on PRs.
- [x] Remove destructive seeder behavior (`migrate:fresh` on failure) from `database/seeders/DatabaseSeeder.php`.
- [x] Update middleware error handling to avoid converting internal errors into `401 Unauthorized`.
- [x] Add integration tests for policy evaluation correctness (allow/deny cases).
- [x] Add tests for UUID/string primary keys and custom key names.
- [x] Add tests for route model binding (nested resources and custom route keys).
- [x] Validate cache correctness under policy/chain/check updates (invalidation behavior).

## P1 - Reliability and Safety Hardening

- [x] Add static analysis (Larastan/PHPStan) and enforce a strict level in CI.
- [x] Eliminate hidden DB work in DTO construction (`AccessResult` currently does `count()`).
- [x] Harden default behavior docs and config guidance:
  - [x] `default_policy_behavior` recommendation for enterprise (`deny`).
  - [x] `allow_if_unmatched_route` rollout guidance.
- [x] Add migration guidance for existing installs changing `_id` columns to string.
- [x] Add backward-compatible migration notes for UUID projects.
- [x] Add regression tests for policy fallback behaviors (`allow` vs `deny`).

## P2 - Package Quality and DX

- [x] Align README with actual runtime behavior and available commands.
- [x] Document canonical seeding JSON schema with logical references.
- [x] Add examples for UUID PK + fallback key configuration.
- [x] Add a dedicated "Production Hardening" section in README.
- [x] Add upgrade guide(s) for breaking and non-breaking changes.
- [x] Add release checklist (tests green, changelog updated, upgrade notes ready).

## Architecture Improvements

- [x] Consider reducing dependency surface from `laravel/framework` to `illuminate/*` where feasible.
- [x] Make resource resolver/actor resolver/cache key strategy replaceable via interfaces.
- [x] Relax hardcoded assumptions around app namespaces (`App\\Models\\...`) in validation.
- [x] Add explicit extension hooks for custom policy retrieval and context enrichment.

## Observability and Operations

- [x] Improve structured logging around policy misses and chain evaluation outcomes.
- [x] Add optional metrics hooks (evaluation count, latency, cache hit ratio).
- [x] Add benchmark script/profile for common policy shapes.
- [x] Add docs for recommended cache stores in production (Redis/database tradeoffs).

## Security and Governance

- [x] Add threat model notes (authn/authz boundaries, fail-safe defaults).
- [x] Add secure-by-default config profile.
- [x] Define semantic versioning and compatibility policy.
- [x] Add deprecation policy for config keys and behavior changes.

## Nice-to-Have

- [ ] Generate policy stubs from route-model mappings with richer templates.
- [ ] Add example app fixture for local end-to-end testing.
- [ ] Add contract tests for package consumers integrating custom models.
