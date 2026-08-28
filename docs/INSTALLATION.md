# Installation

This guide covers installation requirements, package layout, runtime storage,
and the minimum checks for a working Goosialize Links installation.

## Requirements

Goosialize Links requires:

| Component | Minimum |
|---|---:|
| Grav CMS | 2.0.12 |
| Admin2 | 2.0.15 |
| Grav API plugin | 1.0.12 |
| PHP | 8.3 |

The Composer platform used by the project is PHP 8.3.0.

## Plugin identity

Plugin slug:

~~~text
goosialize-links
~~~

Expected installation directory:

~~~text
user/plugins/goosialize-links/
~~~

The plugin must not be installed under a different directory name.

## Production dependencies

The release package contains the locked production Composer dependencies needed
by Goosialize Links.

A valid release installation must contain:

~~~text
user/plugins/goosialize-links/vendor/autoload.php
~~~

Do not remove the packaged `vendor/` directory from a release installation.

The release verification suite checks that the packaged production dependency
graph matches the committed source dependency graph.

## Install from a release package

1. Obtain the release archive from the official Goosialize Links repository.
2. Verify the supplied release checksum when available.
3. Extract or install the package.
4. Confirm the final plugin directory is:

~~~text
user/plugins/goosialize-links/
~~~

5. Clear the Grav cache.
6. Open Admin2 and confirm Goosialize Links is visible.
7. Open the public route.

The default public route is:

~~~text
/bio
~~~

## Grav GPM installation

When the desired version is published through Grav GPM, use the normal Grav
package-management workflow.

The GPM-installed source must still include the production dependency files
required by the plugin.

Goosialize Links release verification includes a GPM-style tag-source
installation test.

## Configuration file

Normal plugin configuration is stored at:

~~~text
user/config/plugins/goosialize-links.yaml
~~~

Default configuration:

~~~yaml
enabled: true
route: /bio

profile:
  image: []
  name: ''
  title: ''
  description: ''
  website_url: ''

appearance:
  theme: light
  accent: yellow
  button_shape: rounded
  powered_by: true

actions: []
links: []
~~~

Some text-content keys remain in the normalized configuration contract for
compatibility and fallback handling.

Current editable public profile text, link titles, and action labels are managed
through the Goosialize Links Page under Grav Pages.

## Public Page behavior

Normal plugin initialization does not automatically create or rewrite files
under:

~~~text
user/pages/
~~~

The plugin can resolve an existing physical Goosialize Links Page at the
configured route.

The route lookup is bounded to the configured Page path. It does not recursively
crawl the entire Grav Pages tree on each frontend request.

If Page resolution fails, the failure is handled locally and the normalized
configured route remains available. A Page lookup problem must not take down
unrelated frontend pages.

## Runtime storage

Goosialize Links stores runtime data outside the plugin directory.

Important paths include:

~~~text
user/data/goosialize-links/analytics/
user/media/goosialize-links/profile/
~~~

Keeping runtime data outside the package means plugin updates do not replace
analytics or uploaded profile media.

## Analytics permissions on disk

Analytics use a restricted runtime directory and daily event journal files.

Journal files use mode:

~~~text
0640
~~~

The PHP/web process must have permission to create and update the analytics
directory and files.

Current maintenance analytics use append-only daily `.events` journals for
public events instead of rewriting and fsyncing a complete daily YAML aggregate
on every hit.

Legacy analytics YAML remains readable.

## Profile media

Profile images are uploaded through the native Admin2 file field.

Supported upload media types are:

- JPEG
- PNG
- WebP

The shared profile-media destination is:

~~~text
user://media/goosialize-links/profile
~~~

Saved files are rendered from the corresponding Grav user media path.

## Initial verification

After installation, verify all of the following.

### Admin2

- Goosialize Links appears in the plugin area.
- The plugin editor loads.
- Live Preview loads.
- Native Admin2 configuration controls are available.

### Public page

- The configured public route returns successfully.
- The public page renders the expected profile and enabled items.
- Unsafe configured URLs are rejected by normalization.

### QR

- The primary QR PNG renders.
- The primary QR SVG renders.
- Loading the QR image itself does not count as a QR visit.
- The tracked QR route redirects to the active public page.

### Tracking

- Link tracking resolves only known configured link identities.
- Action tracking resolves only known configured action identities.
- Request-controlled redirect destinations are not accepted.

### Analytics

- Public analytics failures remain fail-open for the visitor experience.
- Recorded events are available to the analytics read model.
- Journal-only days remain visible to reporting.

## Cache

After installing or replacing plugin files, clear the Grav cache before
acceptance testing.

Use the normal Grav cache-clear workflow for the installation.

## Updating an existing installation

Do not delete runtime storage before updating.

Preserve:

~~~text
user/config/plugins/goosialize-links.yaml
user/data/goosialize-links/
user/media/goosialize-links/
~~~

Read [Upgrade](UPGRADE.md) before updating a production installation.

## Uninstalling

Removing the plugin package does not automatically remove:

- plugin configuration
- analytics data
- profile media
- existing Goosialize Links Page content

This is intentional to reduce accidental data loss.

See [Uninstall and Data Retention](UNINSTALL_DATA_RETENTION.md).

## Next steps

After installation:

- [Quick Start](QUICK_START.md)
- [Configuration Reference](CONFIGURATION.md)
- [Admin Guide](ADMIN_GUIDE.md)
- [Compatibility](COMPATIBILITY.md)
- [Troubleshooting](TROUBLESHOOTING.md)
