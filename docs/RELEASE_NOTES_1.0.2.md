# Goosialize Links 1.0.2

Goosialize Links FREE 1.0.2 is a maintenance release focused on runtime
resilience, bounded Page lookup, Admin2 isolation, analytics write
amplification, documentation, and release verification.

The release preserves the existing FREE Link-in-Bio product, public URL
safety, anonymous aggregate analytics model, QR identity, dedicated QR read
permission, and supported dependency floors.

## Runtime hardening

- Public Page resolution now fails safely if the configured Page cannot be
  resolved cleanly.
- Normal plugin boot no longer creates or rewrites native Page files
  automatically.
- Native Page discovery is bounded to the configured route instead of
  recursively scanning the full `user/pages` tree.
- Existing safe HTTP/HTTPS destination normalization and tracked redirect
  protections remain unchanged.

## Appearance

- The `Powered by Goosialize` credit remains enabled by default.
- Administrators can explicitly disable it with `powered_by: false`.
- Missing legacy configuration continues to resolve to the enabled default.

## Admin2 preview isolation

Realtime preview behavior is scoped to the Goosialize Links editor.

The maintenance release does not replace `window.fetch` globally and does not
use document-wide body observation or input behavior for preview updates.

Unsaved preview state remains ephemeral, whitelisted, authenticated, and
separate from persisted configuration.

## Analytics persistence

Public analytics recording uses an append-only daily journal:

~~~text
YYYY-MM-DD.events
~~~

The existing daily YAML format remains supported as a legacy aggregate
baseline.

For a given day, reporting reads the legacy YAML baseline together with any
pending journal events. Journal-only days are also included in analytics date
discovery.

Public event recording does not rewrite the complete YAML aggregate and does
not call `fsync()` for every public hit.

The privacy boundary is unchanged: the FREE core does not create visitor
profiles and does not add IP, user-agent, fingerprint, GeoIP, UTM, or
unique-user analytics.

## Documentation

The repository now includes complete documentation for:

- installation and Quick Start;
- configuration and Admin2 usage;
- public Pages, Links, Actions, appearance, and multilingual content;
- QR code behavior and aggregate analytics;
- privacy, permissions, security, and data storage;
- compatibility, upgrades, uninstall/data retention, and troubleshooting;
- FAQ and examples;
- developer reference and terminology;
- manual browser acceptance and release verification.

The release suite includes a permanent documentation contract that validates
documentation coverage, README navigation, internal links, Markdown
structure, release-status claims, and key source-truth boundaries.

## Release harness

Clean-Grav release acceptance now waits for the pinned Grav runtime to reach a
usable state before the first plugin-initializing CLI operation.

The readiness gate verifies bundled Form/Error plugin autoload requirements
and writable runtime directories before running Grav cache initialization.

This removes the observed startup race from repeated clean-container
acceptance while leaving plugin runtime behavior unchanged.

## Compatibility

Minimum supported versions remain:

- Grav CMS 2.0.12
- Admin2 2.0.15
- Grav API plugin 1.0.12
- PHP 8.3

## Distribution

Release target:

- Tag/title: `1.0.2`
- Asset: `goosialize-links-1.0.2.zip`
- Checksum sidecar: `goosialize-links-1.0.2.zip.sha256`

The final public asset checksum is generated only after the release source is
frozen and the final package is built. Development and pre-release package
hashes are evidence only and must not be presented as the final public asset
checksum.
