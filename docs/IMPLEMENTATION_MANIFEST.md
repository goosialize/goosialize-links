# Implementation Manifest

## 1. Manifest Purpose

This document defines the planned source structure and implementation
boundaries for Goosialize Links FREE.

It is not permission to create every listed path immediately.

Each implementation checkpoint must approve its own exact file inventory.

## 2. Package Root

The repository root is the future Grav plugin package root.

When installed, its contents map to:

`user/plugins/goosialize-links/`

The repository must not add an extra nested `goosialize-links/` directory
around the plugin package.

## 3. Plugin Identity

Locked identity:

- Product name: Goosialize Links
- Plugin slug: `goosialize-links`
- Main plugin class file: `goosialize-links.php`
- Initial edition: FREE
- FREE v1 release-candidate version: `1.0.0-rc.1`
- FREE core license: MIT
- Target Grav baseline: 2.0.12
- Target runtime family: PHP 8.3+
- Default public route: `/bio`

## 4. Planned Root Files

The implementation may eventually include:

- `goosialize-links.php`
- `blueprints.yaml`
- `goosialize-links.yaml`
- `composer.json`
- `languages.yaml`
- `CHANGELOG.md`
- `LICENSE`
- `README.md`

The FREE core is distributed under the MIT License. Future paid addons are
separate products and are outside this repository and its FREE core license.

## 5. Planned Source Directories

The implementation may use:

- `classes/`
- `admin/blueprints/`
- `admin-next/pages/`
- `templates/`
- `assets/css/`
- `assets/js/`
- `tests/`
- `docs/`

No directory should be created before a concrete checkpoint requires it.

## 6. PHP Namespace

The intended PHP namespace root is:

`Goosialize\Links`

Composer autoloading should map the namespace to the approved source directory
once the first namespaced classes are introduced.

The main Grav plugin class remains in the conventional root plugin file unless
the Grav runtime contract requires a different structure.

## 7. Admin2 Strategy

Native blueprint mode is the default for ordinary settings and forms.

Expected Admin2 blueprint location:

`admin/blueprints/`

A page-level JavaScript component under:

`admin-next/pages/`

may be added only when native Admin2 blueprint fields cannot satisfy a locked
requirement.

Custom controls must not be introduced merely for visual preference.

## 8. Public Rendering Strategy

The FREE public page will use:

- a controlled public route;
- server-side data loading;
- Twig rendering;
- mobile-first CSS;
- stable link identifiers;
- explicit click-tracking URLs or events.

The public renderer must remain usable when analytics are disabled.

The public renderer must remain usable when Goosialize Leads is absent.

## 9. Runtime Storage Boundaries

Runtime data is outside this repository.

Planned ownership:

### Grav plugin configuration

`user/config/plugins/goosialize-links.yaml`

### Plugin-managed runtime data

`user/data/goosialize-links/`

Runtime data may include:

- normalized profile data;
- links and ordering;
- generated or cached QR assets;
- analytics counters or events;
- schema or migration state.

The exact storage format is deliberately deferred to the storage-design
checkpoint.

No SQLite, YAML, JSON, or flat-file choice is locked by this manifest.

## 10. Stable Link Identity

Every link must have a durable internal identifier.

The identifier must remain stable when:

- the title changes;
- the destination URL changes;
- the link is reordered;
- the link is temporarily disabled.

Analytics must reference the durable identifier rather than the visible title.

## 11. FREE Analytics Manifest

Only the following event identities are approved:

- `page_view`
- `link_click`
- `action_click`
- `qr_visit`

Only the following FREE reports are approved:

- total page views;
- total link and action clicks;
- clicks per link or action button;
- total QR visits.

No advanced analytics path should be created during the FREE implementation
unless the product contract is amended first.

## 12. QR Manifest

The FREE edition supports one generated QR code.

The QR code points to a controlled internal tracking route.

The route must:

1. recognize only the approved QR identifier;
2. record one `qr_visit`;
3. resolve the configured public page internally;
4. redirect safely;
5. reject or ignore invalid identifiers safely.

The route must not accept an arbitrary external redirect destination.

## 13. Leads Integration Manifest

The core plugin must not require Goosialize Leads.

Future Leads-related source must be isolated behind an adapter or capability
boundary.

No implementation may directly read or write:

- Goosialize Leads storage;
- internal Leads repositories;
- private Leads controller methods;
- provider-addon data.

The final adapter file inventory remains deferred until the Leads public
integration contract is stable.

## 14. Initial Implementation Phases

### Phase 1 — Plugin Skeleton

Expected concerns:

- plugin identity;
- default configuration;
- plugin enablement;
- compatibility metadata;
- bootstrap lifecycle;
- basic syntax validation.

No public page, analytics, QR, or Leads integration belongs in the first
skeleton checkpoint.

### Phase 2 — Storage Contract

Expected concerns:

- profile schema;
- link schema;
- stable identifiers;
- normalization;
- persistence interface;
- migration/version state.

### Phase 3 — Admin2 Foundation

Expected concerns:

- permissions;
- sidebar/page registration;
- native blueprint forms;
- data and save endpoints;
- validation;
- error handling.

### Phase 4 — Public Page

Expected concerns:

- configurable safe route;
- Twig template;
- responsive layout;
- profile rendering;
- link rendering;
- FREE branding;
- accessibility.

### Phase 5 — Public Experience Completion

Expected concerns:

- profile image;
- predefined themes;
- social and contact actions;
- durable action identifiers;
- public-page accessibility and presentation polish.

### Phase 6 — Basic Analytics

Expected concerns:

- approved `page_view`, `link_click`, and `action_click` recording;
- approved aggregate counters;
- clicks per durable link or action ID;
- native Admin2 analytics report;
- privacy controls.

### Phase 7 — QR Code

Expected concerns:

- QR library decision;
- QR generation;
- PNG and SVG output;
- internal `qr_visit` tracking route;
- safe redirect behavior;
- aggregate QR-visits reporting.

### Phase 8 — Leads Adapter

Expected concerns:

- capability detection;
- one basic contact form;
- normalized submission;
- safe unavailable state.

This phase remains blocked until the Leads public contract is stable.

### Phase 9 — Release Readiness

Expected concerns:

- security review;
- permission regression;
- Grav compatibility tests;
- packaging;
- documentation;
- release assets.

## 15. Initial Skeleton Candidate Inventory

The first code checkpoint is expected to consider only the following candidate
paths:

- `goosialize-links.php`
- `blueprints.yaml`
- `goosialize-links.yaml`
- `composer.json`
- `languages.yaml`
- `CHANGELOG.md`

This is a candidate inventory, not yet an approved creation manifest.

The exact Phase 1 file set will be locked immediately before implementation.

## 16. Deferred Decisions

The following decisions remain explicitly open:

- exact storage format;
- exact PHP minimum version within the supported Grav runtime;
- QR generation dependency;
- analytics retention implementation;
- Admin2 page split;
- test framework and test directory structure;
- development wiring into the Grav staging installation;
- final public Leads adapter API.

Open decisions must be resolved in the checkpoint where they first become
necessary.

## 17. Forbidden Early Expansion

Before the relevant phase, do not introduce:

- multi-profile architecture;
- paid feature implementation;
- subscription or licensing enforcement;
- third-party marketing integrations;
- campaign analytics;
- device or geographic tracking;
- custom CSS editors;
- generic page-builder abstractions;
- frontend account systems;
- GoosBoard code or concepts.

## 18. Manifest Amendment Rule

Any new top-level directory, runtime dependency, external service, analytics
event, public route, or cross-plugin dependency requires an explicit manifest
review before implementation.

## Locked Phase 7 QR Implementation Contract

### Identity

The FREE QR identity is fixed to:

- `qr_primary`

No user-created QR identifiers are supported.

### Public routes

The approved public routes are:

- `/<public-route>/qr/qr_primary`
- `/<public-route>/qr/qr_primary/png`
- `/<public-route>/qr/qr_primary/svg`

The PNG and SVG formats use path segments instead of filename extensions so
dynamic image responses are not intercepted by common static-file routing
rules. The responses retain their correct MIME types and `.png` or `.svg`
download filenames.

The tracked route redirects only to the plugin's normalized active public
route. It must ignore and reject request-provided redirect destinations.

PNG and SVG image requests do not count as QR visits.

### Analytics

Phase 7 extends the daily analytics schema with:

- `totals.qr_visits`;
- `qrs.qr_primary`;
- event identity `qr_visit`.

Existing analytics schema version 1 files must remain readable and must resolve
missing QR counters to zero.

The native Admin2 analytics report adds:

- Total QR Visits;
- Primary QR Code visits.

No visitor, device, location, referrer, UTM, campaign, or unique-user data is
recorded.

### QR generation

The approved generator is `endroid/qr-code` version `6.0.9`.

The locked production dependency graph is:

- `endroid/qr-code` `6.0.9`;
- `bacon/bacon-qr-code` `3.1.1`;
- `dasprid/enum` `1.0.7`.

The minimum Composer resolution platform is PHP `8.3.0`.

### Native Admin2 surface

The QR management surface uses the Grav API and Admin2 extension contracts:

- `onApiSidebarItems`;
- plugin-page blueprint context through `onApiBlueprintResolved`;
- `admin/blueprints/goosialize-links.yaml`;
- `admin-next/pages/goosialize-links.js`.

The page must use native Admin2 form and design-system components. It may render
the generated QR image, but it must not replace native fields with imitated
custom HTML controls or use Shadow DOM.

The Admin2 surface provides:

- public tracked QR URL;
- QR preview;
- PNG download;
- SVG download;
- current aggregate QR visit count.

### Release packaging

The source repository commits `composer.lock`.

The development repository does not commit generated `vendor/` contents.
Production dependencies are installed and included during release packaging.

### Admin2 QR Page Implementation Decision

Phase 7 uses the following exact Admin2 architecture:

- sidebar event: `onApiSidebarItems`;
- plugin-page blueprint context: `onApiBlueprintResolved`;
- API route registration: `onApiRegisterRoutes`;
- blueprint path:
  `admin/blueprints/goosialize-links.yaml`;
- page script path:
  `admin-next/pages/goosialize-links.js`;
- Admin2 route:
  `/admin/plugin/goosialize-links`.

The page is a constrained component plugin page rather than a blueprint-only
page because the locked requirements include an image preview and binary PNG
and SVG downloads.

The implementation may add:

- one read-only QR page-data API endpoint;
- one permission-gated PNG download endpoint;
- one permission-gated SVG download endpoint;
- one API controller dedicated to the QR Admin2 surface;
- one native plugin-page blueprint;
- one Admin2 page script.

The page-data response may expose only:

- `qr_id`;
- `tracked_url`;
- `preview_url`;
- `png_download_url`;
- `svg_download_url`;
- `qr_visits`.

The implementation must not add:

- save or mutation endpoints;
- user-defined QR identifiers;
- custom QR destinations;
- custom QR styling;
- custom HTML form controls;
- Shadow DOM;
- Goosialize Leads dependencies.

All page and API access must be authenticated and permission-gated.

#### Permission Contract

The dedicated plugin permission is:

- `api.goosialize-links.qr.read`.

The sidebar item's `authorize` value and all three custom controller methods
must use this exact permission.

The standard API permission `api.config.read` remains required by the installed
API plugin for:

- `GET /blueprints/plugins/goosialize-links/pages/goosialize-links`.

The two permissions have different responsibilities:

- `api.config.read` allows the native Admin2 blueprint to load;
- `api.goosialize-links.qr.read` allows access to Goosialize Links QR data and
  downloads.

No QR create, update, delete, style, destination, or campaign permission is
created.

#### Exact API Route Inventory

The plugin registers exactly these collector-relative routes through
`onApiRegisterRoutes`:

- `GET /goosialize-links/qr`;
- `GET /goosialize-links/qr/download/png`;
- `GET /goosialize-links/qr/download/svg`.

The routes have the following fixed responsibilities:

1. `GET /goosialize-links/qr`
   returns the read-only QR page view model.
2. `GET /goosialize-links/qr/download/png`
   returns a generated PNG download.
3. `GET /goosialize-links/qr/download/svg`
   returns a generated SVG download.

No `POST`, `PUT`, `PATCH`, or `DELETE` QR route is allowed.

The page-data endpoint returns exactly:

- `qr_id`;
- `tracked_url`;
- `preview_url`;
- `png_download_url`;
- `svg_download_url`;
- `qr_visits`.

The fixed values and URL responsibilities are:

- `qr_id` is always `qr_primary`;
- `tracked_url` points to
  `/<public-route>/qr/qr_primary`;
- `preview_url` points to the existing non-tracking public PNG endpoint
  `/<public-route>/qr/qr_primary/png`;
- `png_download_url` identifies
  `/goosialize-links/qr/download/png`;
- `svg_download_url` identifies
  `/goosialize-links/qr/download/svg`;
- `qr_visits` is the aggregate `qrs.qr_primary` count.

The controller constructs all URLs server-side. No route accepts a QR
identifier, destination URL, redirect URL, output style, campaign, filename, or
filesystem path from request input.
