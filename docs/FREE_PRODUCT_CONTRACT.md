# Goosialize Links FREE Product Contract

## Public Page

- Exactly one public Link in Bio page
- Default route: `/bio`
- Configurable safe route
- Mobile-first responsive layout
- Required Goosialize Links branding

## Profile

- Logo or profile image
- Name or brand name
- Short title
- Short description
- Optional website action

## Links

- Maximum eight active links
- Title
- Valid HTTP or HTTPS URL
- Same-tab or new-tab behavior
- Enable or disable state
- Stable ordering
- Durable internal link ID

## Social and Contact Actions

The FREE v1 supports:

- Instagram
- Facebook
- TikTok
- LinkedIn
- YouTube
- X
- WhatsApp
- Email
- Telephone
- Website

Each action has a durable internal action ID so analytics remain stable when
the visible label or value changes.

## Business Information — 1.1.0 Target

Goosialize Links 1.1.0 may add the following bounded FREE business
information to the single public page:

- one weekly Working Hours schedule;
- one optional Google Maps Directions URL.

### Working Hours

Working Hours are stored per day in deterministic Monday-through-Sunday order.
Each day contains:

- `enabled`: whether the business is open that day;
- `open`: one 24-hour `HH:MM` opening time when enabled;
- `close`: one 24-hour `HH:MM` closing time when enabled.

`enabled` is the approved state name because it follows the existing plugin
enable/disable convention and avoids inverted `closed` logic. An enabled day
requires both times and its closing time must be later than its opening time.
A disabled day is rendered as closed and does not require times.

Admin2 rejects an enabled day with a missing, malformed, equal, or reversed
interval. Runtime normalization fails closed: a malformed enabled day produces
no active interval and cannot emit unvalidated text. Disabled days discard any
stale time values.

The initial contract supports exactly one continuous same-day interval per
day. The public display may group consecutive days with identical normalized
hours, but storage remains per-day. The section is omitted when no Working
Hours configuration exists. An explicitly persisted schedule whose seven days
are disabled is configured data and renders as closed; a missing or empty
schedule remains absent so existing configurations do not gain new output.

### Google Maps Directions

The optional `business.google_maps_url` field accepts only a valid HTTPS Google
Maps URL in one of these forms: `www.google.com/maps/...`,
`maps.google.com/...`, `maps.app.goo.gl/...`, or `goo.gl/maps/...`. It does not
accept other Google services, arbitrary location URLs, or redirect URLs.
Admin2 rejects an invalid value, while runtime normalization omits an invalid
Directions action safely.

When configured, it is rendered as a map-pin action labelled `Directions` in
English and `Οδηγίες` in Greek. It opens in a new tab with
`rel="noopener noreferrer"`. It uses no Maps JavaScript API, API key, embedded
map, geocoding, coordinates, or visitor-location access.

Directions uses the existing `action_click` tracking event and a plugin-owned
reserved action identity. It does not create a configurable social-action type
or a new analytics event. Working Hours create no analytics event.

### Explicitly Deferred

The 1.1.0 Business Information contract excludes:

- split shifts or multiple intervals per day;
- holiday, exception, or seasonal schedules;
- public-holiday services or external opening-hours APIs;
- appointments or booking;
- timezone-aware `Open now` or `Closed now` status;
- Google Business Profile synchronization;
- embedded maps, geocoding, coordinates, GPS, or map analytics.

## Design

The FREE v1 exposes only bounded predefined design options:

- three predefined themes:
  - Light
  - Dark
  - Sunrise
- five predefined accent colors:
  - Yellow
  - Blue
  - Coral
  - Green
  - Purple
- three predefined button shapes:
  - Square
  - Rounded
  - Pill

Not included:

- arbitrary background colors
- arbitrary text colors
- arbitrary button colors
- configurable spacing controls
- custom CSS
- custom fonts
- video backgrounds
- advanced animation controls
- advanced theme builder

## QR Code

The FREE edition includes one QR code for the active public Link in Bio page.

Included:

- one durable QR identifier: `qr_primary`;
- tracked QR visits;
- PNG output;
- SVG output;
- native Admin2 preview;
- PNG download;
- SVG download;
- total QR visits in the analytics report.

Not included:

- multiple QR codes;
- campaigns;
- custom destinations;
- QR logos;
- custom QR colors or shapes;
- advanced QR analytics.

## Analytics

The FREE dashboard contains only:

- Total Page Views
- Total Link and Action Clicks
- Clicks per Link/Action Button
- QR Visits

Required events:

- `page_view`
- `link_click`
- `action_click`
- `qr_visit`

Not included:

- unique visitors
- device information
- browser information
- geographic information
- referrers
- UTM reports
- funnels
- CSV export
- comparison reports
- multiple campaign analytics

## Contact Form

The FREE edition may expose one basic form containing:

- name
- email
- optional phone
- message
- consent checkbox

Custom fields and multiple forms are excluded.

## Leads Integration

When a compatible Goosialize Leads installation is available, the basic form
may submit a normalized lead containing:

- source: `goosialize-links`
- public route
- form identifier
- originating link or block identifier when relevant
- QR-origin indication when relevant
- consent data
- supported campaign metadata when already present

Goosialize Links must not contain:

- lead status management;
- lead exports;
- provider integrations;
- SendPulse code;
- CRM pipelines;
- mailing-list logic.

## Release Boundary

Anything outside this document requires an explicit contract update before
implementation.
