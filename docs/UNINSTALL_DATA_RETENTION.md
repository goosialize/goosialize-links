# Uninstall and Data Retention

Removing the Goosialize Links plugin package does not automatically delete
site-owned configuration, content, media, or analytics.

This behavior is intentional and reduces accidental data loss.

## Package removal

The plugin application package is installed under:

~~~text
user/plugins/goosialize-links/
~~~

Removing this directory removes the plugin code.

It does not imply removal of site-owned runtime data stored elsewhere.

## Configuration retention

Plugin configuration is stored at:

~~~text
user/config/plugins/goosialize-links.yaml
~~~

Removing the plugin package does not automatically delete this file.

## Page content retention

Public profile text, link titles, and action labels can be stored in Grav Page
content under:

~~~text
user/pages/
~~~

Removing the plugin package does not automatically delete existing Page files.

This is important because Pages are site-owned content.

## Profile media retention

Uploaded profile media is stored under:

~~~text
user/media/goosialize-links/profile/
~~~

Removing the plugin package does not automatically delete uploaded profile
images.

## Analytics retention

Analytics are stored under:

~~~text
user/data/goosialize-links/analytics/
~~~

Current maintenance analytics can include:

~~~text
YYYY-MM-DD.events
~~~

Legacy installations can also contain:

~~~text
YYYY-MM-DD.yaml
~~~

Removing the plugin package does not automatically delete either format.

## Migration backup retention

Legacy migration/provisioning backups can exist under:

~~~text
user/data/goosialize-links/migration-backups/
~~~

These backups are also site-owned data and are not automatically deleted by
package removal.

## Why data is retained

Automatic deletion during uninstall could destroy:

- configuration;
- localized Page content;
- uploaded media;
- historical analytics;
- rollback/migration data.

Goosialize Links therefore separates package removal from site-data deletion.

## Disable versus uninstall

Disabling the plugin is different from deleting its package.

Disabling keeps the plugin installed but inactive according to the plugin
runtime configuration.

Neither disabling nor package removal should be treated as automatic data
erasure.

## Manual full removal

If the site operator explicitly wants to remove all Goosialize Links data,
review and remove only the intended paths.

Typical paths are:

~~~text
user/config/plugins/goosialize-links.yaml
user/data/goosialize-links/
user/media/goosialize-links/
~~~

Page content requires separate review because:

~~~text
user/pages/
~~~

can contain unrelated site content.

Do not delete the full Pages tree merely to remove Goosialize Links.

## Removing Goosialize Links Page content

Identify the exact physical Goosialize Links Page associated with the configured
route.

Review:

- translated Page variants;
- custom Page route metadata;
- neighboring unrelated Pages.

Delete only the Goosialize Links Page files that the site operator explicitly
intends to remove.

## Analytics deletion

If analytics should be erased, remove the relevant files from:

~~~text
user/data/goosialize-links/analytics/
~~~

This can include both `.events` journals and legacy `.yaml` baselines.

Do not assume deleting only one format removes all historical analytics.

## Profile media deletion

If profile media should be erased, review:

~~~text
user/media/goosialize-links/profile/
~~~

and remove the intended files.

Do not delete unrelated media elsewhere under `user/media`.

## Retention policy

Goosialize Links does not enforce a universal retention period.

The site operator should decide how long to retain:

- aggregate analytics;
- migration backups;
- configuration backups;
- profile media;
- Page content.

The appropriate period depends on operational and legal requirements.

## Privacy responsibility

The current analytics model is aggregate and does not create visitor profiles,
but analytics files are still site data.

Site operators should define:

- retention duration;
- backup retention;
- access control;
- deletion procedure;
- privacy-notice wording where applicable.

See [Privacy](PRIVACY.md).

## Backup before manual deletion

Before deleting retained data, consider creating a protected backup if rollback
or audit recovery may be required.

Backups can contain site content and should not be published to the plugin
repository.

## Reinstall behavior

If Goosialize Links is reinstalled while retained configuration/data remains,
the plugin may find the existing site-owned state again.

This can be useful for recovery but should be understood before reinstalling on
a site where a clean reset was intended.

## Clean reset

For a deliberate clean reset:

1. back up anything that may need recovery;
2. remove the plugin package;
3. remove the Goosialize Links configuration;
4. remove Goosialize Links analytics;
5. remove Goosialize Links profile media;
6. review and remove only the exact Goosialize Links Page content;
7. review migration backups;
8. clear Grav cache;
9. reinstall only if a fresh installation is desired.

## Do not delete blindly

Never use a broad removal such as deleting the entire:

~~~text
user/pages/
user/media/
user/data/
~~~

trees for a Goosialize Links uninstall.

Those directories can contain data owned by Grav, the site, and other plugins.

## Verification after uninstall

After package removal, verify:

1. `user/plugins/goosialize-links/` is removed if full uninstall was intended;
2. the public Goosialize Links runtime is no longer active;
3. retained configuration matches the chosen retention policy;
4. retained analytics match the chosen retention policy;
5. retained profile media match the chosen retention policy;
6. no unrelated Page or media content was removed.

## Verification after full data deletion

If a complete removal was intended, verify:

1. plugin package absent;
2. plugin config absent;
3. analytics directory absent;
4. profile media absent;
5. exact Goosialize Links Page content absent;
6. migration backups absent if explicitly selected for deletion;
7. unrelated site data remains intact.

## Related documentation

- [Data Storage](DATA_STORAGE.md)
- [Privacy](PRIVACY.md)
- [Upgrade](UPGRADE.md)
- [Installation](INSTALLATION.md)
- [Security](SECURITY.md)
