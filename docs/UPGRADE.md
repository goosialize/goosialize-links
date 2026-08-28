# Upgrade

This guide describes how to update Goosialize Links while preserving
site-owned configuration, Page content, media, and analytics.

## Before upgrading

Back up the current site before replacing plugin files.

Important Goosialize Links paths include:

~~~text
user/config/plugins/goosialize-links.yaml
user/pages/
user/media/goosialize-links/
user/data/goosialize-links/
~~~

The plugin package itself is:

~~~text
user/plugins/goosialize-links/
~~~

## Package replacement

Upgrade the application/package files without deleting site-owned runtime data.

Do not replace or remove the site's:

- plugin configuration;
- Page content;
- profile media;
- analytics;
- migration backups.

## Production dependencies

Released Goosialize Links packages include their locked production Composer
dependencies.

After upgrading, verify:

~~~text
user/plugins/goosialize-links/vendor/autoload.php
~~~

still exists.

Do not reuse a partial package that omits the required `vendor/` tree.

## Cache

After replacing plugin files, clear the Grav cache before acceptance testing.

## Configuration compatibility

The current maintenance line preserves the existing stable configuration
contract.

The default configuration still includes:

~~~yaml
enabled: true
route: /bio
~~~

Existing link and action identities should be preserved.

Do not regenerate IDs simply because the plugin version changes.

## Powered by compatibility

The `appearance.powered_by` setting was added after the original stable
configuration.

Older configurations that do not contain the key remain compatible.

A missing key behaves as enabled.

To disable the public credit explicitly:

~~~yaml
appearance:
  powered_by: false
~~~

## Existing Page content

Normal plugin boot does not automatically recreate or rewrite existing Page
files.

Existing Goosialize Links Page content should remain site-owned across upgrades.

The current Page lookup is bounded to the configured route.

## Analytics compatibility

Existing analytics data must be preserved.

Legacy daily analytics can exist as:

~~~text
YYYY-MM-DD.yaml
~~~

Current maintenance analytics use:

~~~text
YYYY-MM-DD.events
~~~

The current read model supports both formats.

If both formats exist for the same date, the read model merges the YAML baseline
with the journal events.

## Do not convert analytics manually

Do not manually rewrite legacy YAML into journal files as part of a normal
upgrade.

The current maintenance code provides backward-compatible reading without
requiring a destructive migration.

## Profile media compatibility

Existing profile images remain under:

~~~text
user/media/goosialize-links/profile/
~~~

Do not delete this directory during package replacement.

## Stable identities

Stable link and action IDs are used by:

- tracking;
- aggregate analytics;
- multilingual display mapping.

Preserving these identities preserves analytics continuity.

Do not regenerate them during an upgrade unless a specific migration procedure
explicitly requires it.

## R2-A behavior change

Current maintenance code no longer performs automatic Page provisioning during
normal plugin boot.

This means frontend requests do not implicitly create or rewrite the public
Page.

Existing Page content is preserved.

## R2-B behavior change

Physical Page route lookup is bounded to the configured route path.

The plugin no longer needs a recursive scan of the complete Page tree on each
frontend request.

## R2-C behavior change

The public Goosialize Links credit can now be disabled.

Backward compatibility is preserved because older configurations with no
`powered_by` key behave as enabled.

## R2-D behavior change

Admin2 Live Preview behavior is scoped to the Goosialize Links editor.

The current maintenance implementation no longer:

- replaces global `window.fetch`;
- attaches document-wide input/change listeners;
- observes the entire `document.body`;
- runs body-wide text rewriting.

No special upgrade action is required for this change.

## R2-E behavior change

Current maintenance analytics use an append-only daily event journal for new
public events.

The public hit path no longer rewrites and fsyncs the complete daily YAML
aggregate for every event.

Existing YAML remains readable.

No destructive analytics migration is required.

## Upgrade verification

After upgrading, verify:

1. the plugin loads without PHP errors;
2. Admin2 opens the Goosialize Links editor;
3. existing profile settings remain;
4. existing links remain;
5. existing actions remain;
6. stable IDs remain unchanged;
7. the public route renders;
8. existing Page content remains;
9. the profile image renders;
10. Powered by visibility matches configuration;
11. QR PNG renders;
12. QR SVG renders;
13. tracked link redirects work;
14. tracked action redirects work;
15. tracked QR redirects work;
16. old analytics remain visible;
17. new analytics create `.events` journals;
18. journal-only data appears in reporting.

## Rollback

If an upgrade must be rolled back:

1. restore the previous verified plugin package;
2. preserve site-owned configuration and runtime data;
3. restore a site backup if the upgrade included unrelated site changes;
4. clear Grav cache;
5. re-run public/Admin2 acceptance.

Do not delete current analytics or profile media merely to roll back plugin
application files.

## Version-specific release notes

Review the relevant release notes before deploying a new release.

Historical release notes are kept under:

~~~text
docs/RELEASE_NOTES_*.md
~~~

## Production deployment recommendation

For production:

1. back up the site;
2. verify package checksum;
3. replace only plugin package files;
4. clear cache;
5. perform Admin2 acceptance;
6. perform public-page acceptance;
7. verify QR;
8. verify analytics;
9. retain rollback material until acceptance is complete.

## Related documentation

- [Installation](INSTALLATION.md)
- [Compatibility](COMPATIBILITY.md)
- [Data Storage](DATA_STORAGE.md)
- [Analytics](ANALYTICS.md)
- [Uninstall and Data Retention](UNINSTALL_DATA_RETENTION.md)
- [Release Verification](RELEASE_VERIFICATION.md)
