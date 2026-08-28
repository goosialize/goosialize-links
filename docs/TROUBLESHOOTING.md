# Troubleshooting

This guide covers common Goosialize Links installation, Admin2, public-page,
tracking, QR, multilingual, media, and analytics problems.

## Start with the basics

Before changing source code:

1. confirm the plugin is installed;
2. confirm it is enabled;
3. verify the required Grav/Admin2/API/PHP versions;
4. clear Grav cache;
5. reproduce the issue;
6. inspect Grav logs;
7. inspect the browser console for Admin2 problems.

## Plugin does not appear in Admin2

Verify the plugin directory exists:

~~~text
user/plugins/goosialize-links/
~~~

Verify the package contains:

~~~text
blueprints.yaml
goosialize-links.php
vendor/autoload.php
~~~

Confirm Admin2 is installed and meets the required version.

## Public page returns an error

Check:

- plugin enabled state;
- configured route;
- Grav logs;
- Page content;
- conflicting Pages;
- malformed configuration.

Default route:

~~~text
/bio
~~~

## Physical Page resolution errors

Current maintenance code handles Page resolution failures locally.

Normal plugin boot does not automatically create or rewrite Page files.

The Page lookup is bounded to the configured route and does not recursively
crawl the full Page tree on every request.

If logs show Page resolution failure:

1. inspect the exact physical Page directory;
2. inspect language-specific Page files;
3. check for duplicate matching route directories;
4. inspect Page frontmatter;
5. confirm the configured route is valid.

The rest of the site should not be taken down by a local Goosialize Links Page
lookup error.

## Existing Page route conflict

If another unrelated Grav Page uses the same route:

1. identify the conflicting Page;
2. decide which Page should own the route;
3. change the site/Page structure deliberately;
4. do not rely on automatic Goosialize Links overwriting.

## Live Preview does not load

Verify:

- Admin2 is functioning;
- the Grav API plugin is functioning;
- the public route resolves;
- the authenticated user has appropriate access.

Then:

1. use Refresh Preview;
2. reload Admin2;
3. clear Grav cache;
4. inspect browser console;
5. inspect Grav logs.

## Live Preview stops updating

The preview is deliberately scoped to the Goosialize Links editor.

It does not globally patch:

~~~text
window.fetch
~~~

and does not attach document-wide input/change listeners.

If realtime preview stops updating:

1. Save the configuration;
2. Refresh Preview;
3. verify the public page separately;
4. inspect browser console errors;
5. confirm the affected field is part of the preview whitelist.

## Preview token error

Preview state uses a random session-backed token.

If preview-token validation fails:

1. reload the Admin2 editor;
2. allow a new preview session to initialize;
3. do not manually edit preview URLs/tokens;
4. verify the browser session/cookies needed by Admin2 are working.

## Profile image does not render

Verify the file exists under:

~~~text
user/media/goosialize-links/profile/
~~~

Supported upload formats are:

- JPEG
- PNG
- WebP

Then check:

1. the image was saved through Admin2;
2. filesystem ownership/permissions;
3. the stored profile image reference;
4. Grav cache;
5. Grav logs.

## Newly selected profile image not visible in preview

Profile uploads use the native Admin2 file workflow.

A newly selected file may require Save before its stored media path becomes
available to normal public rendering.

Save first, then refresh the preview.

## Link does not appear

Check:

1. link is enabled;
2. URL is valid;
3. URL uses HTTP or HTTPS;
4. Page-owned link title exists;
5. configuration was saved;
6. stable ID has not been manually corrupted.

## Link does not redirect

Check the saved URL and stable link ID.

Tracked redirects resolve the destination from normalized saved state.

The request cannot provide an arbitrary external destination.

Inspect logs for normalization or unknown-identity errors.

## Action does not appear

Check:

1. action is enabled;
2. action type is supported;
3. value is valid for the selected action type;
4. Page-owned action label exists;
5. configuration was saved.

## Email action fails

Verify the configured value is a valid email address.

The normalizer must be able to create the email destination.

## Phone action fails

Verify the number can be normalized to a telephone destination.

Public phone actions use:

~~~text
tel:
~~~

Malformed telephone numbers are rejected.

## WhatsApp action fails

Verify the configured number can be normalized to the supported WhatsApp
format.

The normalized destination uses:

~~~text
https://wa.me/
~~~

## Multilingual content missing

Check:

~~~text
system.languages.supported
system.languages.default_lang
~~~

Then verify:

1. the expected language is enabled;
2. the language-specific Page exists;
3. the localized field has a value;
4. the Page was saved;
5. the active URL/language is correct;
6. Grav cache was cleared.

## Analytics do not increase

Analytics are stored under:

~~~text
user/data/goosialize-links/analytics/
~~~

Current maintenance events are appended to:

~~~text
YYYY-MM-DD.events
~~~

Check:

1. analytics directory is writable;
2. `.analytics.lock` can be created/opened;
3. daily `.events` file exists;
4. journal mode is `0640`;
5. Grav logs do not contain analytics write failures.

## Analytics failure does not break public page

This is expected behavior.

Analytics are fail-open for the public experience.

A storage error is logged but should not intentionally break a valid public
page or tracked redirect.

## Old analytics missing after update

Legacy analytics can exist as:

~~~text
YYYY-MM-DD.yaml
~~~

Current maintenance analytics use:

~~~text
YYYY-MM-DD.events
~~~

The read model supports both.

If old data appears missing:

1. verify the legacy YAML file still exists;
2. confirm the date filename is valid;
3. do not manually rename or merge the file;
4. inspect analytics-read errors in logs.

## Journal-only day missing

The current read model discovers `.events` dates even when no YAML baseline
exists.

If a journal-only date is missing:

1. verify the `.events` filename matches `YYYY-MM-DD.events`;
2. verify the file is readable;
3. inspect it for malformed event lines;
4. inspect reporting logs.

## Malformed analytics journal

Malformed journal data fails locally when read.

Do not silently repair it without first preserving a backup.

Inspect the affected file and compare it with the documented journal contract.

## QR image does not render

Verify:

- package `vendor/` exists;
- PHP requirement is satisfied;
- QR production dependencies are present;
- public route is valid.

Then inspect Grav logs.

## QR image downloads but visit count does not increase

This is expected.

Loading the PNG or SVG image does not record a QR visit.

A visit is counted only through the tracked QR route.

## QR tracked route does not redirect

Verify:

- public route resolves;
- `qr_primary` is the supported QR identity;
- analytics failure is not being confused with redirect failure;
- no route conflict exists.

The QR redirect destination is derived from the active public page.

## QR/Admin analytics page unavailable

Verify the user has:

~~~text
api.goosialize-links.qr.read
~~~

Also verify Admin2 has the platform configuration access it requires.

The current protected workflow can also depend on:

~~~text
api.config.read
~~~

## Unauthorized API response

If an account is denied:

1. verify the exact account;
2. inspect group permissions;
3. confirm the dedicated Goosialize Links read permission;
4. clear cache after permission changes;
5. sign out/in if required.

Do not grant broad unrelated privileges as a shortcut.

## Powered by credit still visible

Verify the saved configuration contains:

~~~yaml
appearance:
  powered_by: false
~~~

Older configuration with no `powered_by` key intentionally defaults to enabled.

## Appearance value rejected

Supported themes:

- light
- dark
- sunrise

Supported accents:

- yellow
- blue
- coral
- green
- purple

Supported button shapes:

- square
- rounded
- pill

Unsupported manually entered values are rejected.

## Plugin update appears to lose data

Do not overwrite site-owned paths during an upgrade.

Preserve:

~~~text
user/config/plugins/goosialize-links.yaml
user/pages/
user/media/goosialize-links/
user/data/goosialize-links/
~~~

See [Upgrade](UPGRADE.md).

## Plugin removed but data remains

This is expected.

Package uninstall does not automatically erase site-owned data.

See [Uninstall and Data Retention](UNINSTALL_DATA_RETENTION.md).

## Useful information when reporting an issue

Include:

- Grav version;
- PHP version;
- Admin2 version;
- Grav API plugin version;
- Goosialize Links version;
- exact failing route;
- relevant Grav log excerpt;
- browser console error if Admin2-related;
- whether the issue reproduces after cache clear.

Do not include:

- passwords;
- API tokens;
- private credentials;
- private production backups;
- unrelated visitor/site data.

## Related documentation

- [Installation](INSTALLATION.md)
- [Admin Guide](ADMIN_GUIDE.md)
- [Live Preview](LIVE_PREVIEW.md)
- [Analytics](ANALYTICS.md)
- [QR Code](QR_CODE.md)
- [Permissions](PERMISSIONS.md)
- [Security](SECURITY.md)
- [Upgrade](UPGRADE.md)
