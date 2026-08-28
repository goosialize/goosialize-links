# Terminology

This document defines the terminology used throughout Goosialize Links
documentation and release verification.

## Goosialize Links

The self-hosted Link in Bio plugin for Grav CMS.

## Public page

The visitor-facing Goosialize Links experience.

The default route is:

~~~text
/bio
~~~

## Plugin configuration

Shared configuration stored under:

~~~text
user/config/plugins/goosialize-links.yaml
~~~

It includes shared settings such as appearance, destinations, stable identities,
and profile-media references.

## Page content

Editorial public content managed through the Goosialize Links Grav Page.

Examples include:

- profile name;
- profile title;
- profile description;
- link titles;
- action labels.

## Shared configuration

Values that are not primarily language-specific editorial content.

Examples include:

- profile image;
- website URL;
- appearance;
- link destination URL;
- action destination;
- stable item identity.

## Stable identity

A persistent internal ID associated with a configured link or action.

Stable identities are used by:

- tracking;
- analytics;
- multilingual mapping.

They are not visitor identifiers.

## Link

A configured public destination displayed on the Link in Bio page.

A link has:

- stable ID;
- enabled state;
- URL;
- new-tab behavior;
- Page-owned visible title.

## Action

A configured social/contact destination.

Supported action types include:

~~~text
website
instagram
facebook
tiktok
youtube
linkedin
x
email
phone
whatsapp
~~~

## Tracked link

A link click routed through Goosialize Links so the plugin can record a
`link_click` before redirecting to the normalized saved destination.

## Tracked action

An action click routed through Goosialize Links so the plugin can record an
`action_click` before resolving the normalized action destination.

## Open redirect

A redirect endpoint where a requester can choose an arbitrary external target.

Goosialize Links tracked routes are designed not to behave as generic open
redirects.

Destinations are derived from normalized server-side plugin state.

## Primary QR

The single QR identity in the current FREE product contract:

~~~text
qr_primary
~~~

## QR image

The PNG or SVG representation of the primary tracked QR URL.

Loading the image does not itself record a QR visit.

## QR visit

An analytics event recorded when the tracked primary QR route is requested.

Event name:

~~~text
qr_visit
~~~

## Page view

An aggregate public page analytics event.

Event name:

~~~text
page_view
~~~

## Link click

An aggregate tracked-link event.

Event name:

~~~text
link_click
~~~

## Action click

An aggregate tracked-action event.

Event name:

~~~text
action_click
~~~

## Aggregate analytics

Count-based analytics without a visitor-profile model.

The current FREE analytics contract records aggregate interactions rather than
unique visitors.

## Visitor profile

A stored or derived analytics representation of an individual visitor.

The current FREE Goosialize Links analytics model does not create visitor
profiles.

## Visitor identifier

A persistent or derived identifier intended to distinguish individual visitors
for analytics purposes.

Stable link/action IDs are not visitor identifiers.

## Analytics journal

The current maintenance append-only daily event file.

Filename format:

~~~text
YYYY-MM-DD.events
~~~

## Legacy analytics baseline

The earlier daily aggregate YAML format.

Filename format:

~~~text
YYYY-MM-DD.yaml
~~~

The current read model remains compatible with this format.

## Journal-only day

An analytics date that has an `.events` journal but no YAML baseline.

Journal-only days are valid and visible to the current reporting model.

## Analytics read model

The logic that reads and aggregates available analytics storage.

It combines legacy YAML and current journal events where required.

## Analytics write path

The runtime path used to record a public analytics event.

Current maintenance behavior appends one small event rather than rewriting a
full YAML aggregate per request.

## Fail-open analytics

The policy that analytics storage failure should not intentionally break a
valid public page or redirect flow.

The error is logged while the visitor-facing operation remains primary.

## Analytics lock

The local synchronization file:

~~~text
.analytics.lock
~~~

Writes use an exclusive lock and reads use a shared lock.

## Live Preview

The Admin2 preview of the real public Goosialize Links rendering path.

Unsaved preview changes are temporary.

## Preview state

Temporary session-backed configuration overlay used only by the protected
editor-preview workflow.

## Preview token

A random 32-character hexadecimal token associated with temporary preview
state.

It is not an analytics visitor identifier.

## Preview whitelist

The explicit set of unsaved fields the preview state is allowed to accept.

Unsupported fields are rejected.

## Scoped Admin2 behavior

Admin2 integration limited to the Goosialize Links editor rather than changing
global browser/Admin2 behavior.

Current maintenance preview code does not replace global `window.fetch` or
observe the full `document.body`.

## Powered by

The public Goosialize Links credit.

Configuration key:

~~~text
appearance.powered_by
~~~

The default is enabled.

## Physical Page

A Grav Page represented by files under:

~~~text
user/pages/
~~~

Goosialize Links can resolve an existing physical Page at the configured route.

## Bounded Page lookup

Physical Page discovery restricted to the configured route path.

It does not mean recursively searching the complete Pages tree on every public
request.

## Page provisioning

Code that can create/migrate Goosialize Links Page content.

Normal plugin boot does not automatically perform provisioning.

## Public route

The local Grav path where the public Goosialize Links page is available.

Default:

~~~text
/bio
~~~

## Effective public route

The normalized route actually used after considering configured route and
supported physical Page routing behavior.

## Protected administration route

An Admin/API route that requires authenticated authorization.

Examples include protected QR, dashboard, and editor-preview endpoints.

## Dedicated permission

The product-specific protected read permission:

~~~text
api.goosialize-links.qr.read
~~~

## Platform configuration permission

The Admin2 platform configuration-read capability:

~~~text
api.config.read
~~~

It has a different responsibility from the Goosialize Links dedicated
permission.

## Read-only QR administration

The current QR administration contract that provides data/download access
without QR create/update/delete operations.

## Runtime data

Site-owned data created or maintained outside the plugin package.

Examples:

~~~text
user/data/goosialize-links/
user/media/goosialize-links/
~~~

## Package data

Application files shipped as part of the Goosialize Links plugin package.

Examples include:

~~~text
goosialize-links.php
classes/
templates/
admin-next/
languages/
vendor/
~~~

## Production dependencies

Composer dependencies required by the released plugin at runtime.

They are included in the public release package.

## GPM-style source install

Verification that the tracked release source can be installed in the shape used
for Grav/GPM distribution.

Expected release-gate marker:

~~~text
GPM-STYLE TAG SOURCE INSTALL = PASS
~~~

## Automated release gates

The complete automated release verification suite.

Expected marker:

~~~text
AUTOMATED RELEASE GATES = PASS
~~~

## Manual browser acceptance

Human verification of browser/UI behavior that cannot be fully proven by static
or automated runtime tests.

Expected completion marker:

~~~text
GOOSIALIZE_LINKS_MANUAL_BROWSER_ACCEPTANCE=PASS
~~~

## Deterministic package

A release archive built from the same approved source in a repeatable package
construction process.

## Package SHA-256

The SHA-256 digest of a specific built package.

An intermediate development package SHA is not automatically the checksum of a
future final public release asset.

## Maintenance release

A backward-compatible release focused on fixes, hardening, packaging, or
documentation rather than introducing unrelated feature-line work.

## FREE core

The current public Goosialize Links product scope described by the FREE product
contract.

Future paid/addon functionality is not part of the current FREE core unless
explicitly implemented and released.

## Source of truth

The current verified source code, metadata, contracts, and tests used to
determine actual product behavior.

Documentation should follow source truth rather than assumptions or roadmap
ideas.

## Related documentation

- [Documentation Index](DOCUMENTATION_INDEX.md)
- [Developer Reference](DEVELOPER_REFERENCE.md)
- [Release Verification](RELEASE_VERIFICATION.md)
- [Architecture Principles](ARCHITECTURE_PRINCIPLES.md)
