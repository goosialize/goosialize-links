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
