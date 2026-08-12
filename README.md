# Goosialize Links

Goosialize Links FREE is a self-hosted Link-in-Bio plugin for Grav CMS. It
creates one mobile-first public link page on your own website, with tracked
links and actions, privacy-preserving aggregate analytics, and one tracked QR
code.

Release candidate: `1.0.0-rc.1`.

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

The default public URL is `/bio`. Ensure no existing Grav page or route uses
the configured route.

## Configuration

Open **Plugins → Goosialize Links** in Admin2 to configure:

- the public route;
- profile image, name, title, description, and website URL;
- one of three themes, five accent colors, and three button shapes;
- social/contact actions;
- up to eight active HTTP or HTTPS links.

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

- **Public page is missing:** confirm the plugin is enabled, the configured
  route is valid and not already owned by another page, then clear Grav cache.
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
