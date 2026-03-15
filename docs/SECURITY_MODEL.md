# Security Model

## Scope

This package evaluates authorization decisions (ABAC). It does not replace authentication.

## Trust boundaries

- Authentication (who the user is) must be established by the host app.
- ABAC middleware evaluates authorization for authenticated requests.
- Policy and attribute data are trusted inputs from your application data model.

## Threat considerations

- Missing policy coverage can create accidental allow paths.
- Overly broad path patterns can map requests to unintended resources.
- Cache keys that omit relevant context can cause decision reuse across dissimilar requests.
- Misconfigured actor resolver method can produce internal errors.

## Fail-safe defaults

Use the hardened defaults from [Operations Guide](OPERATIONS.md).

- Set `ABAC_DEFAULT_POLICY_BEHAVIOR=deny` to avoid permissive fallback when no policy matches.
- Treat `allow` fallback as an explicit migration/rollout choice, not a long-term default.

## Operational controls

- Monitor logs for `abac.policy_miss` and denied chain outcomes.
- Monitor logs for `abac.actor_attributes_empty` to catch missing actor attribute seed/data issues.
- Review route-to-model mappings whenever new endpoints are added.
- Keep middleware attached only to routes with explicit auth requirements.
