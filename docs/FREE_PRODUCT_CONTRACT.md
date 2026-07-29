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
- Valid URL
- Optional supported icon
- Same-tab or new-tab behavior
- Enable or disable state
- Stable ordering
- Durable internal link ID

## Social and Contact Actions

Initial supported actions may include:

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
- Location URL

## Design

- Three predefined themes
- Background color
- Text color
- Button background color
- Button text color
- Rounded or square button style
- Basic spacing controls

Not included:

- custom CSS
- custom fonts
- video backgrounds
- advanced animation
- advanced theme builder

## QR Code

- One QR code for the public page
- Admin preview
- PNG download
- SVG download
- Internal tracked redirect
- One aggregate QR-visits metric

Not included:

- multiple QR codes
- campaign QR codes
- QR-specific funnels
- custom embedded logos
- advanced QR styling

## Analytics

The FREE dashboard contains only:

- Total Page Views
- Total Link Clicks
- Clicks per Link/Button
- QR Visits

Required events:

- `page_view`
- `link_click`
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
