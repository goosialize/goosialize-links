# Roadmap

## Phase 0 — Foundation

- Create standalone repository
- Lock product identity
- Lock FREE scope
- Lock architecture principles
- Define draft Leads integration boundary
- Review and commit documentation foundation

## Phase 1 — Plugin Skeleton

- Grav plugin metadata
- Blueprints
- Configuration defaults
- Compatibility declaration
- Permissions
- Lifecycle checks

## Phase 2 — Data Model

- Profile configuration
- Stable link identifiers
- Link ordering
- Validation and normalization
- Storage migrations

## Phase 3 — Native Admin2

- Admin route
- Profile settings
- Link management
- Social and contact actions
- Design settings
- Validation and save states

## Phase 4 — Public Page

- Safe configurable route
- Mobile-first Twig rendering
- Profile header
- Link rendering
- Accessibility
- Required FREE branding

## Phase 5 — Public Experience Completion

- Profile image
- Three predefined themes
- Accent and button-shape options
- Social and contact actions
- Durable action identifiers
- Public-page presentation regression

## Phase 6 — Basic Analytics

- `page_view`
- `link_click`
- `action_click`
- Total page views
- Total link and action clicks
- Clicks per link or action button
- Native Admin2 Reports integration
- Minimal privacy controls

## Phase 7 — QR Code

- Durable FREE identifier `qr_primary`
- Internal tracked route `/<public-route>/qr/qr_primary`
- PNG endpoint `/<public-route>/qr/qr_primary/png`
- SVG endpoint `/<public-route>/qr/qr_primary/svg`
- `qr_visit`
- Backward-compatible analytics schema extension
- Native Admin2 QR page
- QR preview
- PNG and SVG downloads
- Safe redirect tests
- Aggregate QR-visits reporting

## Phase 8 — Leads Adapter

- Capability detection
- Basic contact form
- Normalized submission adapter
- Safe unavailable state
- Integration tests

This phase begins only after the Goosialize Leads integration contract is
stable.

## Phase 9 — Release Readiness

- Security review
- Permission review
- Compatibility testing
- Functional testing
- Packaging validation
- README and user guide
- Changelog
- FREE release assets

## Phase 10 — 1.1.0 Business Information

- additive optional `business` configuration;
- Working Hours for Monday through Sunday;
- one continuous same-day interval per enabled day;
- strict 24-hour `HH:MM` validation;
- deterministic and accessible EN/EL public rendering;
- optional validated HTTPS Google Maps Directions action;
- existing `action_click` analytics semantics for Directions;
- backward-compatible empty defaults with no migration;
- native Admin2 Business Information section between Profile and Appearance;
- locked Grav 2.0.12 and current-stack regression coverage.

Deferred beyond 1.1.0:

- split shifts, overnight intervals, exceptions, holidays, and seasons;
- timezone-aware `Open now` status;
- appointments and booking;
- Google APIs, Business Profile synchronization, embedded maps, geocoding,
  coordinates, visitor location, or map analytics.

## Future Commercial Addon

Deferred paid functionality includes:

- multiple pages;
- unlimited links;
- removal of branding;
- advanced themes;
- custom CSS;
- scheduled links;
- multiple QR campaigns;
- multiple and custom forms;
- advanced analytics;
- exports;
- additional content blocks.
