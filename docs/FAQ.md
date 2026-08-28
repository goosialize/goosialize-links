# FAQ

## What is Goosialize Links?

Goosialize Links is a self-hosted Link in Bio plugin for Grav CMS.

It provides a public profile page, links, social/contact actions, QR support,
multilingual content, Live Preview, and aggregate analytics.

## What is the default public route?

The default route is:

~~~text
/bio
~~~

## What versions are required?

Minimum requirements are:

- Grav CMS 2.0.12
- Admin2 2.0.15
- Grav API plugin 1.0.12
- PHP 8.3

See [Compatibility](COMPATIBILITY.md).

## Does the release package include Composer dependencies?

Yes.

A valid release installation includes:

~~~text
user/plugins/goosialize-links/vendor/autoload.php
~~~

Do not remove the packaged production `vendor/` tree.

## Where is plugin configuration stored?

~~~text
user/config/plugins/goosialize-links.yaml
~~~

## Where is public text content edited?

Current public profile text, link titles, and action labels are managed through
the Goosialize Links Page under Grav Pages.

## Does the plugin automatically create the Page on every request?

No.

Normal plugin boot does not automatically create or rewrite Page files.

## Does it scan the entire Pages tree on every request?

No.

Current physical Page lookup is bounded to the configured route.

## What happens if Page lookup fails?

The failure is handled locally.

The plugin keeps the normalized configured route rather than intentionally
taking down unrelated frontend routes.

## Where is the profile image stored?

~~~text
user/media/goosialize-links/profile/
~~~

## Which profile image formats are supported?

- JPEG
- PNG
- WebP

## Is the profile image multilingual?

No separate image is required per language.

The profile image is shared across languages.

## Which themes are available?

- Light
- Dark
- Sunrise

## Which accent colors are available?

- Yellow
- Blue
- Coral
- Green
- Purple

## Which button shapes are available?

- Square
- Rounded
- Pill

## Can I remove the Powered by credit?

Yes.

Set:

~~~yaml
appearance:
  powered_by: false
~~~

The setting defaults to enabled.

Older configurations without the key also behave as enabled.

## Does the FREE version support custom CSS?

No arbitrary custom CSS editor is part of the current FREE appearance contract.

## Which action types are supported?

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

## How are phone actions rendered?

Phone actions normalize to:

~~~text
tel:
~~~

## How are WhatsApp actions rendered?

WhatsApp values normalize to:

~~~text
https://wa.me/
~~~

## Can link and action labels be translated?

Yes.

Visible profile text, link titles, and action labels can vary by language while
the same stable identities are preserved.

## Why are stable link/action IDs important?

They are used by:

- tracked routes;
- aggregate analytics;
- multilingual mapping.

Changing a visible title or label does not need to create a new analytics
identity.

## Can I manually change the stable IDs?

You should not.

Manually replacing IDs can break tracking and analytics continuity.

## What analytics are recorded?

The current event types are:

~~~text
page_view
link_click
action_click
qr_visit
~~~

## Does analytics track unique visitors?

No.

The current FREE analytics model records aggregate event counts.

## Does it track IP addresses?

No IP analytics are part of the current FREE analytics contract.

## Does it track browser or device data?

No.

The current FREE analytics model does not provide browser/device analytics.

## Does it fingerprint visitors?

No.

## Does it track country or GeoIP?

No.

## Does it report UTM parameters?

No.

UTM source, medium, and campaign reporting are outside the current FREE
analytics contract.

## Does it use an external analytics service?

No.

Current analytics are stored locally in the Grav installation.

## Where are analytics stored?

~~~text
user/data/goosialize-links/analytics/
~~~

## What is the current analytics file format?

New public events use:

~~~text
YYYY-MM-DD.events
~~~

## Are old YAML analytics still supported?

Yes.

Legacy files named:

~~~text
YYYY-MM-DD.yaml
~~~

remain readable.

## What happens when YAML and `.events` exist for the same day?

The current read model merges the YAML baseline with the pending journal
events.

## Does every analytics hit rewrite the whole YAML file?

No.

Current maintenance code uses append-only journal writes for new public events.

## Does every hit call `fsync()`?

No.

The current public event path does not call `fsync()` for every analytics hit.

## What permissions do analytics journals use?

The current journal mode is:

~~~text
0640
~~~

## What happens if analytics storage fails?

Analytics are fail-open for the public experience.

A write failure is logged and should not intentionally break the valid public
page or redirect.

## How many QR codes are included?

The current FREE contract includes one primary QR identity:

~~~text
qr_primary
~~~

## Does loading the QR image count as a QR visit?

No.

Loading/downloading PNG or SVG does not record a `qr_visit`.

A QR visit is counted through the tracked QR route.

## Which QR formats are available?

- PNG
- SVG

## Can the QR route be used as an open redirect?

It is not intended to.

The destination is derived from normalized plugin state rather than a
request-provided external target.

## What permission is required for QR/analytics administration?

~~~text
api.goosialize-links.qr.read
~~~

## Is there a QR write permission?

No.

The current FREE QR administration contract is read/download oriented.

## Does Admin2 need another permission?

The Admin2 plugin configuration workflow also depends on the platform
configuration-read capability:

~~~text
api.config.read
~~~

## Does Live Preview use the real public template?

Yes.

The preview uses the real public rendering path with the internal preview mode.

## Does Live Preview globally replace `window.fetch`?

No.

Current maintenance code keeps preview behavior scoped to the Goosialize Links
editor.

## Does Live Preview observe the entire `document.body`?

No.

The current implementation avoids document-wide preview observers and listeners.

## Is unsaved preview state permanent?

No.

Preview state is temporary and session-scoped.

## Is the preview token an analytics visitor ID?

No.

The preview token exists only for the protected editor preview workflow.

## Does uninstall delete my data?

No.

Removing the plugin package does not automatically delete configuration, Page
content, analytics, media, or migration backups.

See [Uninstall and Data Retention](UNINSTALL_DATA_RETENTION.md).

## Can I completely remove all Goosialize Links data?

Yes, but data deletion is a separate explicit operation.

Review and remove only the intended Goosialize Links paths.

Do not delete the entire `user/pages`, `user/media`, or `user/data` trees.

## Will an upgrade preserve my analytics?

The maintenance design preserves compatibility with legacy YAML and current
journal files.

Always back up site-owned data before upgrading.

## Do I need to manually convert old analytics YAML?

No.

Normal maintenance upgrade does not require a destructive manual conversion.

## Does Goosialize Links guarantee GDPR compliance?

No plugin can guarantee site-wide legal compliance by itself.

The site operator remains responsible for lawful basis, privacy notices,
retention, access controls, backups, and the rest of the site's technology
stack.

## Where should I start if something is broken?

See [Troubleshooting](TROUBLESHOOTING.md).

## Where can I see the full documentation?

See [Documentation Index](DOCUMENTATION_INDEX.md).
