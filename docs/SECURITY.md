# Security

Goosialize Links uses bounded configuration, validated destinations, scoped
Admin2 behavior, protected administration endpoints, and privacy-preserving
analytics.

This document describes the current verified security boundaries.

## URL validation

User-configured web destinations are normalized before use.

The supported web schemes are:

~~~text
http
https
~~~

Unsupported schemes are rejected.

This applies to normal link destinations and web-based social/contact actions.

## Link redirect safety

Tracked links identify a known configured link by stable internal ID.

The final external destination comes from normalized saved plugin state.

The public request does not supply an authoritative arbitrary external redirect
destination.

Tracked link routes are therefore not intended to operate as generic open
redirects.

## Action redirect safety

Tracked actions follow the same model.

The request identifies a known configured action.

The destination is derived from normalized server-side configuration.

Unknown or unsafe tracking paths are rejected.

## QR redirect safety

The primary QR route records a QR visit and redirects to the active public Link
in Bio page.

The request does not supply an arbitrary external redirect target.

This prevents the tracked QR endpoint from becoming an open redirect.

## Primary QR identity

The current supported QR identity is:

~~~text
qr_primary
~~~

Unexpected QR identities are rejected.

## Public template escaping

Public values are normalized before they reach the view model.

Twig output is escaped at the rendering boundary.

This includes relevant text and HTML-attribute output used by the public
template.

## Route normalization

The configured Goosialize Links route is normalized as a local Grav route.

Unsafe route input is rejected.

The plugin does not interpret the public route configuration as an arbitrary
external URL.

## Physical Page lookup

Goosialize Links can resolve an existing physical Page at the configured route.

The lookup is bounded to the configured Page path.

It does not recursively crawl the entire Grav Pages tree on each frontend
request.

If Page resolution fails, the exception is handled locally and the normalized
configured route remains available.

A malformed or ambiguous Goosialize Links Page should not take down unrelated
site routes.

## No automatic Page writes on public requests

Normal plugin boot does not automatically create or rewrite Page files.

This prevents ordinary frontend traffic from becoming an implicit content-write
operation.

Page provisioning code remains separate from normal public initialization.

## Admin2 isolation

The Goosialize Links Live Preview is scoped to the plugin editor.

It does not replace:

~~~text
window.fetch
~~~

It does not attach document-wide input/change listeners.

It does not observe the entire:

~~~text
document.body
~~~

subtree.

It does not use a body-wide text-node traversal for its editor behavior.

This reduces interference with unrelated Admin2 components.

## Preview state

Unsaved preview state uses a random 32-character hexadecimal token generated
server-side.

Preview state is stored in the authenticated session.

The token is validated before use.

Unsupported preview fields are rejected.

Arbitrary preview routes are rejected.

The token does not replace normal user authorization.

## Protected administration APIs

QR, dashboard, and preview administration routes operate inside the protected
Admin/API context.

The dedicated Goosialize Links read permission is:

~~~text
api.goosialize-links.qr.read
~~~

The normal Admin2 configuration workflow also depends on the platform access
required by Admin2.

See [Permissions](PERMISSIONS.md).

## Unauthorized access

Protected QR/analytics endpoints must deny users who do not have the required
authorization.

Release acceptance verifies unauthorized, authorized, and superuser behavior.

## Read-only QR administration

The current FREE QR administration contract is read/download oriented.

There is no QR create/update/delete permission.

The product does not expose arbitrary QR destination mutation through the
current administration API.

## QR image behavior

QR image endpoints generate controlled PNG or SVG output.

Loading the QR image does not increment QR analytics.

The download filenames are controlled by the plugin:

~~~text
goosialize-links-qr.png
goosialize-links-qr.svg
~~~

## Analytics write isolation

Public analytics writes are intentionally small.

Current maintenance code appends one event to the daily journal instead of
rewriting the complete daily YAML aggregate on every hit.

The public path does not call `fsync()` for each event.

This reduces write amplification under traffic.

## Analytics locking

Analytics writes use an exclusive lock.

Analytics reads use a shared lock.

The lock protects the local analytics storage while preserving the append-only
write model.

## Analytics file permissions

Daily journal files use restricted mode:

~~~text
0640
~~~

Runtime data is stored outside the plugin package.

## Analytics privacy boundary

The current FREE analytics model does not require:

- visitor profiles;
- IP analytics;
- HTTP user-agent analytics;
- browser/device analytics;
- fingerprinting;
- GeoIP;
- country analytics;
- referrer analytics;
- UTM analytics;
- unique-user tracking.

See [Privacy](PRIVACY.md).

## Analytics fail-open behavior

Analytics are non-critical to the public visitor experience.

If an analytics write fails, the error is caught and logged.

A storage failure does not intentionally break the valid public page or redirect
flow.

## Profile media

Profile uploads use the native Admin2 file field.

Accepted media types are limited to:

- JPEG
- PNG
- WebP

Stored filenames are randomized and overwrite avoidance is enabled.

The public resolver restricts profile-image resolution to the expected
Goosialize Links media area.

## Production dependencies

Release packages include the locked production Composer dependency graph.

The release gate verifies:

- installed dependency versions;
- required vendor licenses;
- absence of unexpected development packages;
- absence of unexpected vendor symlinks;
- absence of executable vendor files outside the allowed contract;
- secret-sensitive material scanning.

## Secret scanning

The release verification suite scans source/package content for high-signal
secret material.

Secrets, credentials, tokens, or production visitor data must not be committed
to the public repository.

## Configuration and runtime separation

The plugin package is not the storage location for site-owned runtime data.

Important site-owned paths include:

~~~text
user/config/plugins/goosialize-links.yaml
user/data/goosialize-links/
user/media/goosialize-links/
~~~

This separation reduces the risk of replacing site data during plugin updates.

## Security verification checklist

Before release, verify:

1. invalid URL schemes are rejected;
2. tracked links cannot be turned into arbitrary open redirects;
3. tracked actions cannot be turned into arbitrary open redirects;
4. tracked QR redirects resolve only to the active public page;
5. unsupported QR identities are rejected;
6. public Twig output remains escaped;
7. malformed Page lookup fails locally;
8. normal public boot does not write Page files;
9. Live Preview remains scoped to the plugin editor;
10. unauthorized Admin/API users cannot read protected QR/analytics data;
11. analytics remain aggregate-only;
12. runtime files retain restricted permissions;
13. the release secret scan passes.

## Reporting a security issue

Do not publish credentials, private site data, or exploit details containing
sensitive production information in a public issue.

Use the project's official support/contact channel for sensitive security
reports when necessary.

## Related documentation

- [Permissions](PERMISSIONS.md)
- [Privacy](PRIVACY.md)
- [Analytics](ANALYTICS.md)
- [QR Code](QR_CODE.md)
- [Live Preview](LIVE_PREVIEW.md)
- [Data Storage](DATA_STORAGE.md)
