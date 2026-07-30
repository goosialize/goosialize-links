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
- PNG endpoint `/<public-route>/qr/qr_primary.png`
- SVG endpoint `/<public-route>/qr/qr_primary.svg`
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
