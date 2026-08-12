# Goosialize Links 1.0.0-rc.1

Goosialize Links FREE is a self-hosted Link-in-Bio plugin for Grav 2.0.12 and
PHP 8.3+. This first public release candidate provides one branded public
profile, tracked links and actions, aggregate first-party analytics, and one
tracked QR code with PNG and SVG downloads.

## Highlights

- Native Grav Admin2 configuration and QR/dashboard experience
- Collapsible Profile, Appearance, Social & Contact Actions, and Links editor
  groups with human-readable collection headers
- Responsive same-origin preview of the real public template, with manual and
  Save-triggered refresh plus a safe Open Public Page control
- Physical native Grav Page ownership for the public route, location,
  editorial content, and translations
- Native English/default and Greek Page editing through Admin2's language
  workflow, with non-destructive idempotent provisioning and legacy fallback
- Authenticated realtime preview for supported unsaved shared settings while
  the saved public Page remains unchanged until Save
- Shared profile-image rendering across EN/EL, safe after-Save handling for new
  uploads, and a clean fallback for invalid or missing images
- Stable Link and Action identities across translated labels, keeping
  cross-language analytics aggregated
- Up to eight active links plus supported social/contact actions
- Aggregate page views, clicks, per-item counts, and QR visits
- Safe internal tracked redirects without visitor profiling
- One durable QR identifier: `qr_primary`
- Deterministic package with locked production QR dependencies
- MIT-licensed FREE core

The Admin2 preview/editor experience and multilingual behavior were accepted
in real browsers at 1440, 1024, 900, 768, and 390 pixels. This remains a
release candidate for public evaluation rather than the final `1.0.0` release.
The complete automated suite, clean Grav 2.0.12 package installation, native
Page provisioning, and human-browser acceptance all pass on this RC state.

Scheduled analytics email reports and other paid functionality are not part of
the FREE core or this repository.

## Release contract

- Tag: `1.0.0-rc.1`
- Asset: `goosialize-links-1.0.0-rc.1.zip`
- Checksum: release-generated external
  `goosialize-links-1.0.0-rc.1.zip.sha256` sidecar
- Source: <https://github.com/goosialize/goosialize-links>
- Changelog: <https://github.com/goosialize/goosialize-links/blob/1.0.0-rc.1/CHANGELOG.md>

Install the archive as `user/plugins/goosialize-links/`, confirm packaged
`vendor/` dependencies are present, enable the plugin, and clear the Grav
cache. See the README for configuration, permissions, privacy, and data
retention details.

The checksum sidecar is generated only after this packaged documentation is
frozen, preventing the ZIP from containing a checksum of itself.
