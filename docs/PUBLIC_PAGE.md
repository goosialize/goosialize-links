# Public Page

The Goosialize Links public page is the self-hosted Link in Bio experience
rendered by the plugin.

## Default route

The default public route is:

~~~text
/bio
~~~

The configured route is normalized as a local Grav route.

It is not treated as an arbitrary external redirect destination.

## Page resolution

Goosialize Links can resolve an existing physical Goosialize Links Page at the
configured route.

Normal plugin boot does not automatically create or rewrite Page files.

The physical Page lookup is bounded to the configured path and does not
recursively scan the full Grav Pages tree on every request.

If Page resolution fails, the failure remains local and the plugin falls back
to the normalized configured route.

## Public content sources

The public page combines shared plugin configuration with Page-owned editorial
content.

Shared plugin configuration includes:

- profile image;
- primary website URL;
- appearance settings;
- enabled actions and their destinations;
- enabled links and their destinations;
- stable analytics identities.

Page-owned content includes:

- profile name;
- profile title;
- profile description;
- link titles;
- action labels.

## Profile image

The profile image is shared across language versions.

Saved profile media is stored under:

~~~text
user/media/goosialize-links/profile/
~~~

Supported formats are:

- JPEG
- PNG
- WebP

If a saved profile image cannot be resolved safely, the renderer uses the
defined fallback behavior rather than exposing an unsafe path.

## Website link

The optional profile website URL is rendered only after normalization.

Supported web schemes are:

~~~text
http
https
~~~

## Actions

Enabled actions render as social or contact destinations.

Supported action types include:

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

Action destinations are normalized according to their type.

See [Actions](ACTIONS.md).

## Links

Enabled links render as tracked public destinations.

Each link has a stable internal identity used by tracking and analytics.

The displayed title can change without changing the analytics identity.

See [Links](LINKS.md).

## Appearance

The current appearance model supports:

Themes:

- Light
- Dark
- Sunrise

Accents:

- Yellow
- Blue
- Coral
- Green
- Purple

Button shapes:

- Square
- Rounded
- Pill

The public Goosialize Links credit can be enabled or disabled.

See [Appearance](APPEARANCE.md).

## Multilingual behavior

The public renderer uses the active Grav language.

Localized profile text, link titles, and action labels are used when available.

Fallback content is used when a localized value is unavailable.

Stable link and action identities are preserved across languages so aggregate
analytics remain associated with the same item.

See [Multilingual Content](MULTILINGUAL.md).

## Tracking behavior

Public link clicks and action clicks use internal tracked routes.

The resolver matches known configured identities and derives the final
destination from normalized plugin state.

Request-provided arbitrary redirect targets are not accepted.

## QR behavior

The primary tracked QR route records one `qr_visit` and redirects to the active
public page.

The QR image endpoints themselves do not record QR visits.

See [QR Code](QR_CODE.md).

## Analytics

Public analytics record aggregate first-party event categories:

- `page_view`
- `link_click`
- `action_click`
- `qr_visit`

Current maintenance storage writes events to an append-only daily journal.

Analytics failures are fail-open for the public visitor experience: analytics
storage problems are logged and do not intentionally take down the public page
or redirect flow.

See [Analytics](ANALYTICS.md).

## Privacy boundary

The FREE analytics model does not provide visitor profiles or unique-user
tracking.

It also does not provide reporting based on:

- IP address;
- browser or user agent;
- device fingerprint;
- country;
- referrer;
- UTM parameters;
- campaign identity.

See [Privacy](PRIVACY.md).

## HTML output safety

User-controlled public values pass through normalization before they reach the
view model.

Twig output is escaped at the rendering boundary.

Configured URLs are validated before being exposed as destinations.

## Powered by credit

The Goosialize Links public credit is enabled by default.

It can be disabled through:

~~~yaml
appearance:
  powered_by: false
~~~

Older configurations without this setting retain enabled behavior.

## Public page verification

After configuration changes, verify:

1. the public route returns successfully;
2. the correct profile content is displayed;
3. enabled actions render;
4. enabled links render;
5. unsafe URLs are rejected;
6. tracking redirects resolve to the saved destinations;
7. the active language renders the expected localized content;
8. Powered by visibility matches the saved setting;
9. analytics failures do not break the page.

## Related documentation

- [Quick Start](QUICK_START.md)
- [Admin Guide](ADMIN_GUIDE.md)
- [Configuration Reference](CONFIGURATION.md)
- [Actions](ACTIONS.md)
- [Links](LINKS.md)
- [Appearance](APPEARANCE.md)
- [Multilingual Content](MULTILINGUAL.md)
- [QR Code](QR_CODE.md)
- [Analytics](ANALYTICS.md)
- [Privacy](PRIVACY.md)
