# Data Storage

Goosialize Links separates plugin source code from site-owned configuration,
content, media, and analytics data.

This separation is part of the product's upgrade, privacy, and operational
safety model.

## Storage overview

Important paths include:

~~~text
user/config/plugins/goosialize-links.yaml
user/pages/
user/media/goosialize-links/profile/
user/data/goosialize-links/analytics/
user/data/goosialize-links/migration-backups/
~~~

The plugin package itself is stored under:

~~~text
user/plugins/goosialize-links/
~~~

Runtime data should not be treated as disposable plugin-package content.

## Plugin configuration

Shared plugin configuration is stored at:

~~~text
user/config/plugins/goosialize-links.yaml
~~~

This includes shared settings such as:

- enabled state;
- public route;
- profile image reference;
- profile website URL;
- appearance settings;
- action destinations and identities;
- link destinations and identities.

Current editable public text content is Page-owned rather than primarily stored
as shared plugin configuration.

## Page content

Public editorial content is managed through Grav Pages.

This includes:

- profile name;
- profile title;
- profile description;
- link titles;
- action labels.

Goosialize Links can resolve an existing physical Page at the configured route.

Normal plugin boot does not automatically create or rewrite Page files.

## Page lookup

Physical Page lookup is bounded to the configured Goosialize Links route.

The plugin does not recursively scan the complete `user/pages` tree on every
frontend request.

A Page lookup failure remains local and falls back to the normalized configured
route.

## Profile media

Uploaded profile media is stored under:

~~~text
user/media/goosialize-links/profile/
~~~

The Admin2 upload contract permits:

- JPEG;
- PNG;
- WebP.

Stored filenames are randomized and overwrite avoidance is enabled.

The profile image is shared across language versions.

## Analytics directory

Analytics are stored under:

~~~text
user/data/goosialize-links/analytics/
~~~

This path is outside the plugin package so updates do not overwrite analytics.

## Current analytics journal

Current maintenance analytics use daily append-only event journals.

Filename format:

~~~text
YYYY-MM-DD.events
~~~

Example:

~~~text
2026-08-28.events
~~~

Each public analytics event appends one journal line.

## Analytics lock

Analytics synchronization uses:

~~~text
.analytics.lock
~~~

Writes use an exclusive lock.

Reads use a shared lock.

The lock coordinates local concurrent access to the analytics files.

## Journal file permissions

Daily analytics journals use:

~~~text
0640
~~~

The analytics lock file also uses restricted permissions.

The analytics directory must be writable by the PHP/web runtime user.

## Legacy analytics YAML

Older analytics data can exist as:

~~~text
YYYY-MM-DD.yaml
~~~

The current read model keeps this format readable.

If both a YAML baseline and an event journal exist for one date, the read model
merges the baseline with the pending journal events.

## Journal-only days

A valid analytics date can exist only as:

~~~text
YYYY-MM-DD.events
~~~

A YAML baseline is not required.

Journal-only dates remain discoverable by the analytics reporting layer.

## No rewrite-per-hit requirement

The public analytics write path does not need to rewrite a complete daily YAML
aggregate for every event.

It also does not call `fsync()` for each public analytics hit.

This reduces write amplification under traffic.

## Migration backups

Legacy migration/provisioning support can use:

~~~text
user/data/goosialize-links/migration-backups/
~~~

Backups are site-owned data and are separate from the plugin package.

## Release package ownership

A released plugin package contains application source and locked production
dependencies.

It must not contain site-specific runtime data such as:

- real analytics files;
- uploaded profile images;
- local plugin configuration;
- production Page content;
- credentials;
- backups containing private site data.

## Upgrade behavior

Updating the plugin package must preserve site-owned data.

Important paths to retain include:

~~~text
user/config/plugins/goosialize-links.yaml
user/pages/
user/media/goosialize-links/
user/data/goosialize-links/
~~~

Do not replace these paths with package contents during an upgrade.

See [Upgrade](UPGRADE.md).

## Uninstall behavior

Removing the plugin package does not automatically delete site-owned data.

Potential retained data includes:

- plugin configuration;
- Page content;
- analytics journals;
- legacy analytics YAML;
- profile media;
- migration backups.

This behavior reduces accidental data loss.

See [Uninstall and Data Retention](UNINSTALL_DATA_RETENTION.md).

## Backups

Site backups should include the Goosialize Links site-owned paths if the site
operator wants to preserve the full Link in Bio state.

At minimum, consider:

~~~text
user/config/plugins/goosialize-links.yaml
user/pages/
user/media/goosialize-links/
user/data/goosialize-links/
~~~

Backup access should be protected because Page/profile content can contain
site-operator supplied information.

## Privacy considerations

The analytics directory contains aggregate interaction data.

The current FREE analytics model does not require visitor profiles, but site
operators should still define:

- retention;
- backup policy;
- access control;
- deletion procedures.

See [Privacy](PRIVACY.md).

## Files that belong to the package

Typical package-owned paths include:

~~~text
goosialize-links.php
blueprints.yaml
goosialize-links.yaml
permissions.yaml
classes/
templates/
admin-next/
languages/
vendor/
~~~

These are application/package files.

## Files that do not belong in source control

Do not commit site-specific runtime data to the public plugin repository.

Examples include:

- real analytics;
- local user configuration;
- uploaded customer/site profile media;
- production Page content;
- credentials;
- access tokens;
- private backups.

## Troubleshooting storage

If analytics cannot be written:

1. confirm the analytics directory exists or can be created;
2. confirm the PHP/web user has write permission;
3. inspect ownership and directory mode;
4. inspect `.analytics.lock`;
5. inspect the daily `.events` file;
6. check Grav logs for fail-open analytics errors.

If a profile image does not render:

1. confirm it was saved successfully;
2. verify the file exists under the expected profile-media path;
3. confirm the stored file type is supported;
4. inspect filesystem ownership/permissions;
5. clear Grav cache if required.

If old analytics do not appear:

1. confirm legacy `.yaml` files remain present;
2. confirm their date filenames are valid;
3. verify current `.events` files are present where expected;
4. do not manually merge or rewrite files while diagnosing.

## Related documentation

- [Installation](INSTALLATION.md)
- [Configuration Reference](CONFIGURATION.md)
- [Analytics](ANALYTICS.md)
- [Privacy](PRIVACY.md)
- [Security](SECURITY.md)
- [Upgrade](UPGRADE.md)
- [Uninstall and Data Retention](UNINSTALL_DATA_RETENTION.md)
