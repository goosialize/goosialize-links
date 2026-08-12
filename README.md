# Goosialize Links

Goosialize Links FREE is a self-hosted Link-in-Bio plugin for Grav CMS. It
creates one mobile-first public link page on your own website, with tracked
links and actions, privacy-preserving aggregate analytics, and one tracked QR
code.

Release candidate: `1.0.0-rc.1`.

- Source: <https://github.com/goosialize/goosialize-links>
- Releases: <https://github.com/goosialize/goosialize-links/releases>
- Documentation: <https://github.com/goosialize/goosialize-links#readme>
- Product page: pending; no goosialize.com product-page slug has been approved

## Requirements

- Grav 2.0.12
- PHP 8.3 or newer
- Grav Admin2

Compatibility claims are limited to the tested Grav 2.0.12 baseline.

## Installation

1. Extract the release archive so the plugin is located at
   `user/plugins/goosialize-links/`.
2. Confirm that `user/plugins/goosialize-links/vendor/autoload.php` exists.
   Official release packages include the locked QR dependencies; a source
   checkout does not include generated `vendor/` files.
3. Clear the Grav cache with `php bin/grav clearcache` from the Grav root.
4. Sign in to Admin2 and enable **Goosialize Links** under Plugins, or set
   `enabled: true` in `user/config/plugins/goosialize-links.yaml`.

On first enablement, the plugin provisions a physical native Grav Links Page,
normally at `/bio`. That Page becomes the authority for its route, slug,
location, editorial content, and translations. Change its route later through
Grav's normal Page controls rather than through plugin configuration.

## Configuration

Open **Plugins → Goosialize Links** in Admin2 to configure shared behavior:

- the language-neutral profile image and website URL;
- one of three themes, five accent colors, and three button shapes;
- social/contact actions;
- up to eight active HTTP or HTTPS links.

Edit the physical **Goosialize Links** Page under **Pages** for the displayed
name, title, description, translated Link titles, and translated Action labels.
The default/English Page and its Greek translation use Grav's native Admin2
language switcher. Link destinations, Action values, and their durable IDs are
shared across translations.

The editor uses four native collapsible groups: **Profile**, **Appearance**,
**Social & Contact Actions**, and **Links**. Action and Link collection headers
use their human-readable labels or titles; durable internal IDs remain hidden
from the normal editor presentation.

On desktop, the editor places a sticky preview of the real public template
beside the configuration form. Smaller screens use a single-column layout.
**Refresh Preview** reloads the preview without reloading Admin2, **Open Public
Page** opens its same-origin route, and a successful Save refreshes the preview
automatically. Unsaved Theme, Accent, Button shape, website URL, and supported
shared Link/Action structural changes update the authenticated preview before
Save. This temporary state is ephemeral and non-persistent: the actual public
Page remains unchanged until Save. Native Page editorial translations are
previewed from their saved Page content, not as unsaved realtime overlays.

When Grav multilingual support is enabled, English/default and Greek public
content is managed through the native Page translation workflow. URLs,
tracking routes, and durable Link and Action IDs remain shared across
languages, so analytics aggregate by stable identity instead of translated
labels.

Clean and legacy installations provision the native Page non-destructively.
Existing base and translated content can be imported, provisioning is
idempotent, and existing physical translations are not silently overwritten.
Legacy localized overlay data is retained only as a compatibility fallback
during this release-candidate architecture.

Each link and action has a durable internal ID. Keep these IDs stable when
editing or reordering items so their aggregate analytics remain associated
with the same item.

The public page is available at the configured route, normally `/bio`.
Required Goosialize Links branding remains visible in the FREE edition.

## Admin2 QR and analytics

Authorized users can open **QR Code** in the Admin2 navigation at
`/admin/plugin/goosialize-links`. The page provides:

- total page views;
- total link and action clicks;
- clicks per link or action;
- total QR visits;
- a seven-day aggregate chart;
- Top Links and Top Actions;
- a preview of the single FREE QR code;
- PNG and SVG downloads.

The durable QR identifier is `qr_primary`. With the default public route, its
tracked URL is `/bio/qr/qr_primary`. Opening that URL records one `qr_visit`
and redirects to the active public page. Loading its PNG or SVG image does not
record a visit. A QR visit means the tracked URL was opened; it does not claim
to detect a physical scan.

## Permissions

The QR page, dashboard data, and download API require:

`api.goosialize-links.qr.read`

Admin2 also needs the platform permission `api.config.read` to load the plugin
page blueprint. Superusers inherit access. Do not grant these permissions to
users who should not see aggregate dashboard or QR information.

## Storage and privacy

Configuration is stored by Grav at:

`user/config/plugins/goosialize-links.yaml`

Plugin analytics are stored outside the plugin package under:

`user/data/goosialize-links/analytics/`

Profile uploads are stored under:

`user/media/goosialize-links/profile/`

The profile image is shared across languages. A saved image renders on both
the English and Greek Pages. If Admin2 cannot safely expose a newly selected
binary before upload, the existing saved image remains visible and the new
image appears after Save. Missing or invalid image data falls back to the
initial avatar without emitting a broken image.

FREE analytics store only daily aggregate counters for `page_view`,
`link_click`, `action_click`, and `qr_visit`. The core does not provide visitor
profiles, unique-user tracking, device/browser/country reports, referrers, UTM
reports, funnels, or campaign analytics. Site operators remain responsible for
their privacy notice, lawful basis, backups, retention policy, and access to
the Grav filesystem.

## Upgrade and uninstall

Back up the Grav site before upgrading. Replace the plugin directory with the
new release package, preserve the site-owned configuration and data paths
above, and clear the Grav cache. Review `CHANGELOG.md` before each upgrade.

Disabling or removing the plugin does **not** delete configuration, analytics,
or uploaded profile media. This protects data from accidental loss. To remove
all Goosialize Links data, first back up anything required, then deliberately
delete the plugin configuration, `user/data/goosialize-links/`, and
`user/media/goosialize-links/` from the Grav installation. These runtime paths
are not part of the release archive.

## Troubleshooting

- **Public page is missing:** confirm the plugin is enabled, locate the native
  Goosialize Links Page under Pages, confirm its route is not in conflict, then
  clear Grav cache.
- **QR preview or download fails:** confirm packaged `vendor/` dependencies
  exist and the user has both required permissions.
- **Admin2 shows 401/403:** sign in again and review the user's API and plugin
  permissions.
- **Analytics do not change:** use tracked `/go/`, `/action/`, or
  `/qr/qr_primary` routes. Direct destination and QR image requests are not
  tracked as clicks or QR visits.
- **Profile image is missing:** confirm the upload exists in the documented
  media path and is JPEG, PNG, or WebP.

## FREE scope

The FREE core includes one public page, up to eight active links, supported
social/contact actions, three basic themes, one QR code, aggregate analytics,
and required branding. It is independent from the private GoosBoard project
and works without Goosialize Leads. A future optional basic contact-form
adapter requires a separately compatible Goosialize Leads installation.

Paid functionality is outside this repository and outside the scope of this
FREE core distribution. It includes multiple profiles/campaigns, advanced
analytics, custom CSS or advanced themes, branding removal, multiple/custom QR
codes, and scheduled daily, weekly, or monthly analytics email reports.

## License

Goosialize Links FREE core in this repository is distributed under the MIT
License. See `LICENSE`. Future paid addons are separate products and are not
granted or licensed by this repository's FREE core license.
