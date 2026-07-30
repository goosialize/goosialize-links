# Architecture Principles

## 1. Independent Core

Goosialize Links is an independent Grav plugin.

Links, profile settings, design configuration, QR configuration, and analytics
must not require Goosialize Leads.

## 2. Optional Leads Adapter

Goosialize Leads integration must be isolated behind an adapter or capability
boundary.

The core plugin must degrade safely when Goosialize Leads is:

- absent;
- disabled;
- incompatible;
- temporarily unavailable.

## 3. Site-Specific Public Page

The FREE edition exposes one configurable public route.

The route belongs to the Grav website where the plugin is installed and must
not depend on an external Goosialize-hosted service.

## 4. Native Admin2

Administrator pages and fields use the real Grav Admin2 design system and
supported extension points.

Custom frontend controls must not be used merely to reproduce existing Admin2
components.

## 5. Small Block Model

The FREE edition should use the smallest data model that supports:

- profile content;
- ordinary links;
- social and contact actions;
- ordering;
- enable or disable state;
- analytics identity.

The initial implementation must not introduce a generic page-builder framework.

## 6. First-Party Analytics

Analytics are stored and processed locally.

The FREE edition records only the minimum information required for:

- total page views;
- total clicks;
- clicks per link;
- QR visits.

No full IP address should be retained for reporting. Any temporary anti-abuse
processing must be minimized and privacy-preserving.

## 7. QR Tracking

The generated QR code points to an internal tracking route.

The tracking route:

1. validates the QR identifier;
2. records one `qr_visit`;
3. issues a safe redirect to the public link page.

The product must describe the metric as a QR visit, not as guaranteed physical
scan detection.

## 8. Secure Redirects

All redirects and user-configured URLs require validation.

Internal QR redirects must not become open redirects.

## 9. Stable Identifiers

Links require durable internal IDs so that renaming or reordering a link does
not destroy its analytics identity.

## 10. Upgradeability

FREE functionality belongs to the core plugin.

Future paid functionality should be added through a separate commercial addon
or a documented extension contract rather than a divergent duplicate codebase.

## 11. Testability

Repositories, analytics recording, QR redirects, and Leads integration must be
designed so they can be tested independently from the visual interface.

## 12. Conservative Scope

Architecture must serve the locked FREE v1 product rather than speculative SaaS
or multi-tenant requirements.

## QR Code Contract

The FREE edition provides one QR code with the durable identifier
`qr_primary`.

The QR code must encode the plugin-owned tracked URL:

- `/<public-route>/qr/qr_primary`

The QR code must never encode a destination supplied through a query string,
form value, or other request-controlled redirect parameter.

The tracked QR route resolves the active public Goosialize Links route
server-side, records one `qr_visit`, and returns a temporary redirect to that
public route.

The QR image endpoints are:

- `/<public-route>/qr/qr_primary/png`
- `/<public-route>/qr/qr_primary/svg`

The format is expressed as a path segment rather than a filename extension so
dynamic QR responses remain routable through common web-server static-file
rules. Responses still use the correct image MIME type and download filename.

Fetching a PNG or SVG image must not record a QR visit. Only a request to the
tracked QR route records `qr_visit`.

QR generation is locked to:

- `endroid/qr-code` `6.0.9`;
- `bacon/bacon-qr-code` `3.1.1`;
- `dasprid/enum` `1.0.7`;
- PHP platform `8.3.0`;
- the GD extension for PNG output.

`composer.lock` is committed for reproducibility. Development source does not
commit generated `vendor/` files. The final distributable plugin package must
include production Composer dependencies.

The QR feature must not introduce:

- multiple QR codes;
- custom QR payloads;
- logos embedded in the QR image;
- styling controls;
- campaign parameters;
- query-controlled redirects;
- visitor profiling.

## Admin2 QR Page Architecture Decision

The QR management page uses the native Admin2 plugin-page extension contract.

The selected architecture is a constrained component plugin page:

- native Admin2 plugin-page shell;
- dedicated plugin-page blueprint;
- page-level JavaScript loaded through the supported Admin2 plugin-page
  mechanism;
- authenticated and permission-gated Grav API endpoints;
- native Admin2 typography, spacing, buttons, notices, loading states, and
  error states.

Blueprint-only mode is not sufficient for the locked QR page because the page
must display a generated image preview and provide binary PNG and SVG
downloads.

The page-level component is limited to orchestration and rendering of the QR
preview. It must not introduce:

- custom form controls that imitate Admin2;
- Shadow DOM;
- an independent component or design system;
- custom configuration persistence;
- client-controlled QR destinations;
- client-controlled redirect targets;
- unauthenticated administration data.

The page reads a server-generated view model containing only:

- the tracked QR URL;
- the PNG preview URL;
- the PNG download URL;
- the SVG download URL;
- the aggregate `qr_primary` visit count.

The API remains authoritative for permissions, URL construction, analytics
data, and download responses. Preview and download requests do not record
`qr_visit`.

The dedicated QR administration permission is:

- `api.goosialize-links.qr.read`.

This permission controls the sidebar item, the page-data endpoint, and both
download endpoints. No QR write permission is created because the Phase 7
Admin2 page is read-only.

The Admin2 plugin-page blueprint endpoint separately requires the platform
permission `api.config.read`. That platform permission is a prerequisite for
loading the native plugin-page blueprint, but it does not replace the dedicated
Goosialize Links QR permission.
