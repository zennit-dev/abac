# Governance and Compatibility

## Semantic versioning policy

This package follows SemVer:

- MAJOR: backward-incompatible API/config/behavior changes.
- MINOR: backward-compatible features.
- PATCH: backward-compatible fixes and internal improvements.

## Compatibility commitments

- Public package APIs and documented config keys are treated as compatibility surfaces.
- New behavior defaults should remain backward-compatible in minor/patch releases.
- Security hardening defaults that can change behavior should be called out in release notes.

## Deprecation policy

When a key or behavior is superseded:

1. Mark it deprecated in docs and release notes.
2. Keep support for at least one minor release line.
3. Provide migration guidance and replacement examples.
4. Remove in the next major release.

## Release controls

- Run `composer pint -- --test`, `composer analyse`, and `composer test` before release.
- Update release notes for behavior/config changes.
- Tag releases with semantic versions.
