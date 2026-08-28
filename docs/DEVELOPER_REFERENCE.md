# Developer Reference

This document summarizes the principal Goosialize Links runtime components,
routes, storage contracts, and development boundaries.

It is a technical reference, not a replacement for the source code.

## Plugin entry point

Primary plugin file:

~~~text
goosialize-links.php
~~~

The plugin entry point coordinates:

- Grav plugin initialization;
- public rendering;
- Page resolution;
- analytics initialization;
- tracking routes;
- QR routes;
- protected Admin/API routes;
- Admin2 integration.

## Main classes

Current runtime classes include:

~~~text
classes/AnalyticsReportAggregator.php
classes/AnalyticsStore.php
classes/EditorPreviewController.php
classes/EditorPreviewState.php
classes/LinkCollectionNormalizer.php
classes/LinkPageConfigNormalizer.php
classes/NativePageContentResolver.php
classes/NativePageLocator.php
classes/NativePageProvisioner.php
classes/ProfileImageResolver.php
classes/PublicPageExperienceNormalizer.php
classes/PublicPageViewModelFactory.php
classes/QrAdminController.php
classes/QrCodeGenerator.php
classes/QrRouteResolver.php
classes/SocialActionNormalizer.php
classes/TrackingRouteResolver.php
~~~

## Configuration normalization

`LinkPageConfigNormalizer` owns normalization of the principal plugin
configuration contract.

It validates areas such as:

- route;
- profile values;
- website URL;
- profile translations.

## Link normalization

`LinkCollectionNormalizer` validates configured links.

Responsibilities include:

- stable link IDs;
- enabled state;
- HTTP/HTTPS destination validation;
- new-tab state;
- localized title compatibility data.

## Action normalization

`SocialActionNormalizer` validates social/contact actions.

Supported types include:

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

The normalizer owns type-specific destination construction.

Examples include:

~~~text
tel:
https://wa.me/
~~~

## Appearance normalization

`PublicPageExperienceNormalizer` owns the bounded appearance contract.

Supported themes:

~~~text
light
dark
sunrise
~~~

Supported accents:

~~~text
yellow
blue
coral
green
purple
~~~

Supported button shapes:

~~~text
square
rounded
pill
~~~

It also normalizes the `powered_by` setting.

## Public view model

`PublicPageViewModelFactory` converts normalized configuration and Page content
into the public rendering model.

It preserves:

- stable link/action identity;
- localized display values;
- profile image resolution;
- normalized public destinations;
- appearance configuration.

## Public template

Primary public template:

~~~text
templates/goosialize-links.html.twig
~~~

The template escapes public text and relevant attribute output.

## Physical Page resolution

`NativePageLocator` resolves an existing physical Goosialize Links Page.

Current maintenance behavior is bounded to the configured route.

It must not recursively crawl the entire Pages tree per frontend request.

## Page provisioning

`NativePageProvisioner` remains available for explicit migration/provisioning
logic.

Normal plugin boot does not automatically call Page provisioning.

Public requests therefore do not implicitly create or rewrite Page files.

## Page content resolver

`NativePageContentResolver` overlays Page-owned public content onto normalized
plugin configuration.

Page-owned content includes:

- profile name;
- profile title;
- profile description;
- link titles;
- action labels.

## Profile image resolver

`ProfileImageResolver` resolves the saved shared profile image.

Expected media location:

~~~text
user/media/goosialize-links/profile/
~~~

It also preserves compatibility with the known legacy duplicated profile-media
path shape.

## Tracking resolver

`TrackingRouteResolver` resolves tracked link and action paths.

It accepts known stable identities and resolves destinations from normalized
server-side configuration.

It does not use a request-supplied external redirect destination as the
authoritative destination.

## QR route resolver

`QrRouteResolver` owns public QR route parsing.

Supported QR identity:

~~~text
qr_primary
~~~

Supported image formats:

~~~text
png
svg
~~~

## QR generation

`QrCodeGenerator` generates PNG and SVG QR output.

The destination URL is constrained to supported HTTP/HTTPS schemes.

## QR administration

`QrAdminController` provides the protected QR/dashboard read surface.

It exposes:

- QR metadata;
- analytics dashboard data;
- PNG download;
- SVG download.

Dedicated permission:

~~~text
api.goosialize-links.qr.read
~~~

## Preview controller

`EditorPreviewController` manages protected Live Preview setup and state.

Preview tokens are random 32-character hexadecimal values.

Preview state is stored in the session.

## Preview state validation

`EditorPreviewState` validates unsaved draft overlays.

Unsupported draft keys are rejected.

The preview is intentionally bounded rather than accepting arbitrary plugin
configuration.

## Admin2 preview JavaScript

Primary field implementation:

~~~text
admin-next/fields/goosialize-links-preview.js
~~~

Current maintenance boundaries include:

- no global `window.fetch` replacement;
- no document-wide input/change listeners;
- no full `document.body` observer;
- no body-wide text traversal;
- no sticky preview.

Preview behavior is scoped to the Goosialize Links editor.

## Admin2 page JavaScript

The Admin2 QR/analytics page logic is implemented under:

~~~text
admin-next/pages/goosialize-links.js
~~~

## Analytics store

`AnalyticsStore` owns local analytics persistence.

Current event categories:

~~~text
page_view
link_click
action_click
qr_visit
~~~

## Analytics current journal

New maintenance events are stored in:

~~~text
YYYY-MM-DD.events
~~~

The write path appends one event under an exclusive lock.

It does not dump/rewrite the entire YAML daily aggregate per public hit.

It does not call `fsync()` per hit.

## Analytics legacy compatibility

Legacy analytics use:

~~~text
YYYY-MM-DD.yaml
~~~

`AnalyticsStore::readDate()` merges a legacy baseline and matching pending
journal when both exist.

## Analytics date discovery

`AnalyticsStore::dates()` discovers both YAML and journal dates and deduplicates
them.

Journal-only dates are valid reporting dates.

## Analytics locking

Analytics synchronization uses:

~~~text
.analytics.lock
~~~

Write path:

~~~text
LOCK_EX
~~~

Read path:

~~~text
LOCK_SH
~~~

## Analytics report aggregation

`AnalyticsReportAggregator` aggregates analytics through `AnalyticsStore`.

It does not depend on enumerating only `*.yaml` files.

## Analytics storage path

~~~text
user/data/goosialize-links/analytics/
~~~

Daily journal mode:

~~~text
0640
~~~

## Public analytics failure policy

Public analytics writes are fail-open.

Analytics exceptions are logged without intentionally breaking valid public
page or redirect behavior.

## Privacy boundary

The FREE analytics contract does not include:

- visitor profiles;
- IP analytics;
- user-agent analytics;
- fingerprinting;
- GeoIP/country analytics;
- referrer analytics;
- UTM analytics;
- unique-user analytics.

## Public routes

The effective public Link in Bio route defaults to:

~~~text
/bio
~~~

Tracking and QR paths are derived from the active normalized public route.

## Protected API routes

The current plugin registers protected routes including:

~~~text
/goosialize-links/qr
/goosialize-links/dashboard
/goosialize-links/editor-preview
/goosialize-links/editor-preview/state
/goosialize-links/qr/download/png
/goosialize-links/qr/download/svg
~~~

The exact HTTP methods and authorization requirements are defined in source.

## Permission source

Plugin permissions are defined in:

~~~text
permissions.yaml
~~~

Dedicated plugin permission:

~~~text
api.goosialize-links.qr.read
~~~

Admin2 configuration also depends on the platform configuration-read access
required by the Admin2 runtime.

## Package dependencies

Composer metadata:

~~~text
composer.json
composer.lock
~~~

Production dependencies are committed under:

~~~text
vendor/
~~~

Release packages are expected to include the locked production dependency tree.

## Release tests

Primary release test entry point:

~~~text
tests/release/run-all.sh
~~~

Important supporting tests include:

~~~text
tests/release/static-contract.sh
tests/release/runtime-core.php
~~~

## Static contract

The static release contract verifies areas including:

- package source;
- metadata;
- Admin2 contract;
- Page lookup boundaries;
- preview isolation;
- FREE product boundary;
- analytics journal contract;
- QR/API contract;
- permission contract;
- privacy boundary;
- JavaScript syntax;
- secret scanning;
- diff hygiene.

## Runtime core contract

The runtime core test covers:

- social/contact normalization;
- appearance normalization;
- multilingual content;
- Page provisioning compatibility;
- bounded Page lookup;
- preview state;
- profile image resolution;
- analytics;
- tracking routes;
- QR generation.

## Clean Grav acceptance

The release suite installs the built package into a clean supported Grav
runtime.

It verifies:

- `/bio`;
- QR PNG/SVG;
- tracked QR redirect;
- link redirect;
- action redirect;
- analytics;
- Admin/API authorization;
- packaged dependencies.

## GPM-style source install

The release suite also validates installation from the tracked source shape used
for Grav/GPM-style distribution.

## Development rule

Do not weaken product boundaries merely to make a test pass.

If source behavior and documentation disagree:

1. inspect the current source;
2. identify the intended product contract;
3. fix the incorrect side explicitly;
4. add or update a regression gate.

## Related documentation

- [Architecture Principles](ARCHITECTURE_PRINCIPLES.md)
- [Implementation Manifest](IMPLEMENTATION_MANIFEST.md)
- [Configuration Reference](CONFIGURATION.md)
- [Security](SECURITY.md)
- [Analytics](ANALYTICS.md)
- [Release Verification](RELEASE_VERIFICATION.md)
