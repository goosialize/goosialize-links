# Compatibility

This document describes the current verified runtime and dependency
requirements for Goosialize Links.

## Minimum supported versions

| Component | Minimum |
|---|---:|
| Grav CMS | 2.0.12 |
| Admin2 | 2.0.15 |
| Grav API plugin | 1.0.12 |
| PHP | 8.3 |

These minimum versions are part of the current plugin metadata and release
verification contract.

## Grav compatibility

The plugin metadata declares compatibility with:

~~~text
Grav 2.0
~~~

The current minimum Grav dependency is:

~~~text
>=2.0.12
~~~

## Admin2 compatibility

The minimum Admin2 dependency is:

~~~text
>=2.0.15
~~~

Goosialize Links uses native Admin2 configuration controls and a scoped custom
preview field.

## Grav API plugin compatibility

The minimum Grav API plugin dependency is:

~~~text
>=1.0.12
~~~

Protected QR/dashboard and preview administration routes depend on the Grav API
runtime.

## PHP compatibility

The project requires:

~~~text
PHP >= 8.3
~~~

The Composer platform is pinned to:

~~~text
PHP 8.3.0
~~~

Release syntax checks run against the supported Grav/PHP environment.

## Production Composer dependencies

The current production dependency graph includes the QR-generation dependency
required by the plugin.

Release packages contain the committed production `vendor/` tree.

A release installation must include:

~~~text
user/plugins/goosialize-links/vendor/autoload.php
~~~

## Verified Grav runtime

The automated release gates exercise Goosialize Links on:

~~~text
Grav 2.0.12
~~~

This is the minimum supported Grav version and therefore an important
compatibility baseline.

## Current permanent development/runtime stack

Development and maintenance acceptance has also been exercised on newer Grav 2
components.

The release contract remains tied to the declared minimum versions rather than
assuming that only the newest local runtime is supported.

## Admin2 behavior

Goosialize Links uses:

- native Admin2 form fields;
- the native Save workflow;
- a scoped custom Live Preview field;
- protected API routes;
- Page-centered public content editing.

The Live Preview integration must remain isolated from unrelated Admin2
components.

See [Live Preview](LIVE_PREVIEW.md).

## Browser behavior

The public Link in Bio page is intended for modern browsers capable of standard
HTML, CSS, JavaScript, and responsive layout behavior.

The Admin2 Live Preview depends on the browser capabilities required by the
current Admin2 runtime.

No support claim is made here for legacy browsers that are not supported by the
underlying Grav/Admin2 stack.

## Multilingual compatibility

Goosialize Links uses Grav's configured language system.

It reads:

~~~text
system.languages.supported
system.languages.default_lang
~~~

The plugin does not maintain a separate language registry.

See [Multilingual Content](MULTILINGUAL.md).

## Filesystem requirements

The PHP/web process must be able to read the plugin package and write the
required site-owned runtime paths.

Important writable paths include:

~~~text
user/data/goosialize-links/
user/media/goosialize-links/
~~~

Page editing also depends on the site's normal Grav Pages permissions and
filesystem setup.

## Analytics filesystem behavior

Current maintenance analytics require support for normal local filesystem
operations including:

- file append;
- file locking;
- restricted file permissions.

Analytics journals use:

~~~text
0640
~~~

The implementation does not require an external database or Redis service.

## Web server behavior

The public route and protected API routes depend on the normal Grav web-routing
environment.

Reverse-proxy or HTTPS deployments should preserve a correct site origin so QR
and public URL construction resolve to the expected scheme and authority.

Supported public URL schemes are:

~~~text
http
https
~~~

## Package installation compatibility

The released package is expected to work when installed from:

- a verified release archive;
- Grav/GPM-style tag source when published through GPM.

The release suite verifies both package content and GPM-style source
installation.

## Upgrade compatibility

The current maintenance line preserves compatibility with existing stable
configuration and analytics data.

Examples include:

- older configurations without `appearance.powered_by`;
- legacy analytics YAML;
- existing stable link/action identities;
- existing Page content;
- existing profile media.

See [Upgrade](UPGRADE.md).

## Data compatibility

Legacy analytics files named:

~~~text
YYYY-MM-DD.yaml
~~~

remain readable.

Current maintenance journals use:

~~~text
YYYY-MM-DD.events
~~~

The read model can merge both formats for the same day.

## Unsupported assumptions

Do not assume compatibility with:

- Grav 1.x;
- PHP versions below 8.3;
- missing Admin2 when using the Admin UI;
- missing Grav API plugin when using protected API/Admin2 features;
- package installations that remove required production dependencies.

## Compatibility verification checklist

Before deploying to a new environment, verify:

1. Grav version is 2.0.12 or newer;
2. PHP is 8.3 or newer;
3. Admin2 is 2.0.15 or newer;
4. the Grav API plugin is 1.0.12 or newer;
5. `vendor/autoload.php` exists;
6. the public `/bio` route renders;
7. Admin2 loads the plugin editor;
8. protected QR/dashboard API calls work for an authorized user;
9. QR PNG and SVG rendering work;
10. analytics runtime storage is writable;
11. multilingual routes work if enabled.

## Related documentation

- [Installation](INSTALLATION.md)
- [Upgrade](UPGRADE.md)
- [Admin Guide](ADMIN_GUIDE.md)
- [Permissions](PERMISSIONS.md)
- [Data Storage](DATA_STORAGE.md)
