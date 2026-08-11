# Goosialize Links

Goosialize Links is a self-hosted Link in Bio plugin for Grav CMS.

It creates one mobile-first link page inside the website where the plugin is
installed. The FREE edition includes basic links, social and contact actions,
QR-code generation, simple first-party analytics, and optional integration
with Goosialize Leads.

## Product Positioning

Goosialize Links is:

- installed on one Grav website;
- served from the website's own domain;
- site-specific and self-hosted;
- independent from the private GoosBoard project;
- usable without Goosialize Leads;
- extendable through a future commercial addon.

It is not:

- a SaaS platform;
- a multi-tenant service;
- a hosted profile network;
- a public version of GoosBoard.

## Initial Technical Target

- Plugin slug: `goosialize-links`
- Product edition: FREE
- Grav target: 2.0.12
- PHP target: PHP 8.3+
- Admin interface: native Grav Admin2 components
- Default public route: `/bio`
- Development workflow: reviewed checkpoints with no automatic commits

## Development Status

Goosialize Links FREE has implemented the standalone core through Phase 7:

- Grav 2 plugin foundation and normalized configuration;
- native Admin2 profile, appearance, link and action configuration;
- one mobile-first public Link in Bio page;
- profile image and three predefined themes;
- durable links and social/contact actions;
- privacy-preserving first-party analytics;
- tracked link and action redirects;
- one durable tracked QR code (`qr_primary`);
- PNG and SVG QR output;
- native Admin2 QR management;
- a compact Admin2 analytics dashboard with page views, total clicks,
  QR visits, CTR, a seven-day performance chart, Top Links and Top Actions.

Phase 8, the optional Goosialize Leads adapter and basic contact form, remains
separately gated by the Goosialize Leads public integration contract.

Release-readiness work is now in progress. The current repository version is
pre-release and is not yet the final public FREE v1 package.
