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

## Operational controls

- Monitor logs for `abac.policy_miss` and denied chain outcomes.
- Review route-to-model mappings whenever new endpoints are added.
- Keep middleware attached only to routes with explicit auth requirements.
