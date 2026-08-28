# Admin Guide

Goosialize Links provides native Grav Admin2 editing surfaces for shared plugin
configuration, public Page content, QR tools, and aggregate analytics.

## Admin2 plugin workspace

Open Goosialize Links from the Admin2 Plugins area.

The plugin workspace contains:

1. Live Preview
2. Profile
3. Appearance
4. Social and contact actions
5. Links

The actual configuration controls remain native Admin2 form fields.

## Live Preview

Live Preview renders the real Goosialize Links public template.

The preview implementation is scoped to the Goosialize Links editor area.

It does not:

- replace the browser global `fetch`;
- attach document-wide input listeners;
- attach document-wide change listeners;
- observe the entire `document.body`;
- rewrite unrelated Admin2 text.

The preview supports:

- refresh;
- opening the public page;
- language selection when multiple Grav languages are configured;
- temporary unsaved preview state for supported shared fields.

Preview state is session-scoped and protected by a random token.

See [Live Preview](LIVE_PREVIEW.md).

## Profile section

The Profile section manages shared profile resources.

### Profile image

Upload one image.

Supported media types:

- JPEG
- PNG
- WebP

The saved image is stored under:

~~~text
user/media/goosialize-links/profile/
~~~

The profile image is shared across language versions.

### Website URL

Set the optional primary website destination.

Use a complete HTTP or HTTPS URL.

## Public profile text

The following public text is managed through the Goosialize Links Page under
Grav Pages:

- Profile name
- Profile title
- Profile description

This keeps localized editorial content under the Grav Page content model.

## Appearance section

### Theme

Available themes:

- Light
- Dark
- Sunrise

### Accent

Available accents:

- Yellow
- Blue
- Coral
- Green
- Purple

### Button shape

Available shapes:

- Square
- Rounded
- Pill

### Powered by

The public Goosialize Links credit is enabled by default.

It can be switched off through:

~~~yaml
appearance:
  powered_by: false
~~~

Configurations that predate this setting retain enabled behavior when the key
is absent.

## Social and contact actions

Actions are managed through a native Admin2 list.

Supported types:

- Website
- Instagram
- Facebook
- TikTok
- YouTube
- LinkedIn
- X
- Email
- Phone
- WhatsApp

Each action has:

- enabled state;
- type;
- destination/contact value;
- stable hidden identity.

Public action labels are managed through Page content.

Stable identities are important because aggregate analytics remain attached to
the same action after editing or reordering.

See [Actions](ACTIONS.md).

## Links

Links are managed through a native Admin2 list.

Each link has:

- enabled state;
- destination URL;
- new-tab behavior;
- stable hidden identity.

Public link titles are managed through Page content.

Stable link identities are used by tracking and analytics.

See [Links](LINKS.md).

## Page editor

The Goosialize Links Page blueprint exposes:

- Profile name
- Profile title
- Profile description
- Link titles
- Action labels

Link and action identities remain hidden so editors change display content
without breaking analytics identity.

## Multilingual editing

The public renderer uses the active Grav language.

Localized Page content is used when available.

Fallback content is used when a localized value is unavailable.

Live Preview exposes supported Grav languages using their native language names.

See [Multilingual Content](MULTILINGUAL.md).

## Saving

Use the normal Admin2 Save control.

Supported shared settings can be reflected temporarily in Live Preview before
Save.

After Save:

- stored configuration becomes authoritative;
- temporary preview state is cleared;
- the public route uses saved configuration and Page content.

Profile-image handling follows the native Admin2 file workflow.

## Public route

Default route:

~~~text
/bio
~~~

The effective route may be resolved from an existing physical Goosialize Links
Page.

Normal plugin boot does not automatically create or rewrite the Page.

Page lookup is bounded to the configured path rather than recursively crawling
all Grav Pages on every request.

If Page lookup fails, the failure remains local and the normalized configured
route is used as fallback.

## QR tools

Goosialize Links includes one primary QR identity:

~~~text
qr_primary
~~~

The Admin2 QR surface provides:

- tracked QR URL;
- QR preview;
- PNG download;
- SVG download;
- QR visit totals.

Loading the QR image itself does not record a QR visit.

A QR visit is recorded through the tracked QR route before redirecting to the
active public page.

## QR permission

The dedicated Goosialize Links permission is:

~~~text
api.goosialize-links.qr.read
~~~

It controls the current QR/analytics read surface.

There is no QR write permission in the current FREE product contract.

The Admin2 plugin configuration workflow also depends on the platform
configuration-read permission required by Admin2.

See [Permissions](PERMISSIONS.md).

## Analytics dashboard

The current aggregate analytics categories are:

- Page views
- Link clicks
- Action clicks
- QR visits

Analytics are keyed by stable link/action identities rather than translated
display labels.

The current maintenance storage model writes public analytics events to an
append-only daily journal.

Public hits do not parse, dump, rewrite, and fsync a complete daily YAML
aggregate on every request.

Legacy analytics YAML remains readable by the current read model.

See [Analytics](ANALYTICS.md).

## Privacy model

The FREE analytics model does not provide:

- visitor profiles;
- IP analytics;
- browser/device analytics;
- fingerprinting;
- country analytics;
- referrer analytics;
- UTM reporting;
- unique-user analytics.

See [Privacy](PRIVACY.md).

## Troubleshooting Admin2

If the plugin editor is unavailable:

1. confirm Grav meets the minimum version;
2. confirm Admin2 is installed and enabled;
3. confirm the Grav API plugin is installed and enabled;
4. clear Grav cache;
5. inspect Grav logs.

If QR or analytics administration is unavailable:

1. verify the required permission;
2. verify the Admin2/API dependencies;
3. confirm the plugin is enabled;
4. inspect the authenticated API response.

## Troubleshooting the public page

If the public page does not render:

1. confirm the plugin is enabled;
2. verify the configured route;
3. check for a conflicting unrelated Grav Page;
4. clear the Grav cache;
5. inspect Grav logs for localized Goosialize Links errors.

A physical Page discovery problem should not take down unrelated frontend
routes.

## Related documentation

- [Quick Start](QUICK_START.md)
- [Configuration Reference](CONFIGURATION.md)
- [Public Page](PUBLIC_PAGE.md)
- [Live Preview](LIVE_PREVIEW.md)
- [QR Code](QR_CODE.md)
- [Analytics](ANALYTICS.md)
- [Permissions](PERMISSIONS.md)
- [Troubleshooting](TROUBLESHOOTING.md)
