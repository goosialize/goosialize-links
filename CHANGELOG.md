# Changelog

# 1.0.2
## 08/28/2026

1. [](#improved)
   * Made public Page resolution fail safely when configured Page lookup fails,
     while keeping normal plugin boot free of automatic Page writes.
   * Replaced recursive public Page discovery with bounded lookup along the
     configured route.
   * Added a backwards-compatible `powered_by` configuration toggle so the
     public Goosialize credit can be disabled explicitly.
   * Scoped Admin2 realtime preview behavior to the Links editor instead of
     replacing global `window.fetch` behavior or observing the entire document.
   * Reworked public analytics persistence to use append-only daily event
     journals, avoiding full YAML rewrites and per-hit `fsync()` calls while
     preserving aggregate reporting and legacy YAML compatibility.
   * Added complete public, administrator, privacy, security, analytics,
     troubleshooting, developer, and release documentation with a permanent
     documentation regression contract.
   * Hardened clean-Grav release acceptance with an explicit runtime readiness
     gate for bundled plugin autoload and writable runtime paths.

# 1.0.1
## 08/16/2026

1. [](#improved)
   * Made the released Git source directly installable by Grav GPM by including
     the exact production Composer dependencies locked for QR generation.
   * Added deterministic tag-source installation, committed-vendor safety, and
     source/package parity release gates without changing plugin behavior.

# 1.0.0
## 08/12/2026

1. [](#new)
   * Released the stable Goosialize Links FREE v1 experience with one
     mobile-first Link-in-Bio profile, Links, social/contact Actions, aggregate
     analytics, and one tracked QR code.
   * Included native Grav multilingual Pages with English and Greek content,
     non-destructive provisioning, and authenticated realtime Admin2 preview.
2. [](#improved)
   * Finalized shared profile-image handling, permissions and privacy
     boundaries, deterministic packaging, and explicit supported dependency
     floors for Grav, Admin2, API, and PHP.

# 1.0.0-rc.1
## 08/12/2026

1. [](#new)
   * Added one mobile-first, self-hosted Link-in-Bio profile with bounded FREE
     themes, profile media, required branding, and up to eight active links.
   * Added durable tracked link and social/contact action redirects.
   * Added privacy-preserving page-view and click analytics with per-link and
     per-action aggregate counts.
   * Added one durable QR identity, `qr_primary`, with a safe internal tracked
     redirect.
   * Added PNG and SVG QR preview/download output without image-request
     analytics.
   * Added aggregate `qr_visit` analytics for tracked QR route opens.
   * Added a native Grav Admin2 QR and analytics dashboard with summary
     metrics, seven-day performance, Top Links, and Top Actions.
   * Added a dedicated read-only QR permission and authenticated API
     boundaries.
   * Added English and Greek Admin2 translations.
   * Added English and Greek public profile content with language-aware public
     routes and fallback to existing single-language values.
   * Added a same-origin preview of the real public template with manual
     refresh, Save-triggered refresh, language selection, and a safe public-page
     link.
   * Adopted a physical native Grav Page as the authority for the public route,
     editorial content, and English/Greek translations.
   * Added non-destructive, idempotent native Page provisioning while retaining
     legacy localized overlays as a compatibility fallback.
   * Added authenticated, ephemeral realtime preview for supported unsaved
     shared settings without recording analytics or changing the saved page.
   * Added deterministic release packaging and clean Grav 2.0.12 acceptance
     gates.
2. [](#improved)
   * Grouped the Admin2 editor into native collapsible Profile, Appearance,
     Social & Contact Actions, and Links sections.
   * Added translated native help text to each main plugin configuration group.
   * Replaced normal collection-header IDs with human-readable Action and Link
     labels while preserving the durable internal identities.
   * Added responsive preview/editor behavior for desktop, tablet, and mobile.
   * Corrected profile-image upload and media-path resolution across English
     and Greek, with a clean initial-avatar fallback for invalid or missing
     images and safe after-Save behavior for new binary uploads.
   * Kept translated labels attached to the same Link and Action IDs so
     analytics aggregate across languages.
   * Restricted redirects to normalized configured destinations and rejected
     unsafe or injected redirect targets.
   * Limited analytics storage to anonymous daily aggregate counters; visitor
     profiles, devices, locations, referrers, UTM data, funnels, and unique-user
     reports are not included.
   * Required authentication and explicit permission for QR/dashboard data and
     downloads.
   * Licensed the Goosialize Links FREE core under the MIT License.
   * Kept paid addons, including scheduled analytics email reports, outside
     this repository and outside the FREE core distribution.
