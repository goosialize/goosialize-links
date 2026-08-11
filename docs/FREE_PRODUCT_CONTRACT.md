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
