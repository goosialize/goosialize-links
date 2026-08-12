# Changelog

All notable changes to Goosialize Links are documented in this file.

## [1.0.0-rc.1] - 2026-08-12

### Added

- One mobile-first, self-hosted Link-in-Bio profile with bounded FREE themes,
  profile media, required branding, and up to eight active links.
- Durable tracked link and social/contact action redirects.
- Privacy-preserving page-view and click analytics with per-link and
  per-action aggregate counts.
- One durable QR identity, `qr_primary`, with a safe internal tracked redirect.
- PNG and SVG QR preview/download output without image-request analytics.
- Aggregate `qr_visit` analytics for tracked QR route opens.
- Native Grav Admin2 QR management and analytics dashboard with summary
  metrics, seven-day performance, Top Links, and Top Actions.
- Dedicated read-only QR permission and authenticated API boundaries.
- English and Greek Admin2 translations.
- Deterministic release packaging and clean Grav 2.0.12 acceptance gates.

### Security and privacy

- Restrict redirects to normalized configured destinations and reject unsafe
  or injected redirect targets.
- Store anonymous daily aggregate counters only; no visitor profiles, device,
  location, referrer, UTM, funnel, or unique-user reports are included.
- Require authentication and explicit permission for QR/dashboard data and
  downloads.

### Distribution

- License the Goosialize Links FREE core under the MIT License.
- Keep paid addons, including scheduled analytics email reports, outside this
  repository and outside the FREE core distribution.
