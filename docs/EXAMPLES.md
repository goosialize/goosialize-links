# Examples

This document provides practical examples for common Goosialize Links
configuration and editing workflows.

The examples use the current supported configuration contract.

## Minimal configuration

A minimal enabled installation can use:

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

Public profile text is managed through the Goosialize Links Page.

## Dark theme example

~~~yaml
appearance:
  theme: dark
  accent: yellow
  button_shape: rounded
  powered_by: true
~~~

Supported themes are:

- `light`
- `dark`
- `sunrise`

## Sunrise appearance example

~~~yaml
appearance:
  theme: sunrise
  accent: coral
  button_shape: pill
  powered_by: true
~~~

## Hide the Powered by credit

~~~yaml
appearance:
  powered_by: false
~~~

If the key is absent, the current compatibility behavior treats the credit as
enabled.

## Website action

Conceptual action configuration:

~~~yaml
- id: action_example123456
  enabled: true
  type: website
  value: https://example.com
~~~

The actual generated ID should be preserved rather than manually replaced.

The visible action label is Page-owned content.

## Instagram action

~~~yaml
- id: action_example123456
  enabled: true
  type: instagram
  value: https://instagram.com/example
~~~

Web/social destinations use supported HTTP/HTTPS URLs.

## Email action

~~~yaml
- id: action_example123456
  enabled: true
  type: email
  value: hello@example.com
~~~

Email actions are normalized to an email destination.

## Phone action

~~~yaml
- id: action_example123456
  enabled: true
  type: phone
  value: +35799123456
~~~

The public destination is normalized to:

~~~text
tel:
~~~

## WhatsApp action

~~~yaml
- id: action_example123456
  enabled: true
  type: whatsapp
  value: +35799123456
~~~

The normalized WhatsApp destination uses:

~~~text
https://wa.me/
~~~

## Basic link

~~~yaml
- id: link_example123456
  enabled: true
  url: https://example.com/shop
  new_tab: true
~~~

The visible link title is Page-owned content.

The stable ID should be preserved so tracking and analytics remain attached to
the same logical link.

## Link opening in the same tab

~~~yaml
- id: link_example123456
  enabled: true
  url: https://example.com/contact
  new_tab: false
~~~

## Disabled link

~~~yaml
- id: link_example123456
  enabled: false
  url: https://example.com/archive
  new_tab: true
~~~

Disabled links are not rendered publicly.

## Example public content

Conceptually, the Goosialize Links Page can contain:

~~~yaml
header:
  goosialize_links:
    profile:
      name: Example Company
      title: Digital Studio
      description: Design, development and digital products.
~~~

The exact Page serialization is managed by Grav and the Page blueprint.

## Example multilingual profile

English Page content:

~~~text
Name: Example Company
Title: Digital Studio
Description: Design, development and digital products.
~~~

Greek Page content:

~~~text
Name: Example Company
Title: Ψηφιακό Studio
Description: Σχεδιασμός, ανάπτυξη και ψηφιακά προϊόντα.
~~~

Both language variants can use the same shared profile image and website URL.

## Example multilingual link title

One logical link can keep the same stable identity while showing:

English:

~~~text
Visit our store
~~~

Greek:

~~~text
Επισκεφθείτε το κατάστημά μας
~~~

The link destination and analytics identity remain shared.

## Example multilingual action label

The same WhatsApp action can display:

English:

~~~text
Chat on WhatsApp
~~~

Greek:

~~~text
Μιλήστε μας στο WhatsApp
~~~

The stable action identity remains unchanged.

## Example profile image storage

A profile image uploaded through Admin2 is stored under:

~~~text
user/media/goosialize-links/profile/
~~~

The saved filename may be randomized.

## Example analytics files

Current maintenance journal:

~~~text
user/data/goosialize-links/analytics/2026-08-28.events
~~~

Legacy analytics baseline:

~~~text
user/data/goosialize-links/analytics/2026-08-28.yaml
~~~

The read model can combine both files for the same date.

## Example journal event categories

Possible event categories are:

~~~text
page_view
link_click
action_click
qr_visit
~~~

The journal stores aggregate-event inputs, not visitor profiles.

## Example QR identity

The supported primary QR identity is:

~~~text
qr_primary
~~~

A tracked QR visit records `qr_visit` and redirects to the active public page.

## Example QR image filenames

Protected downloads use controlled filenames:

~~~text
goosialize-links-qr.png
goosialize-links-qr.svg
~~~

Loading the image itself does not record a QR visit.

## Example permission assignment

The dedicated Goosialize Links QR/analytics read permission is:

~~~text
api.goosialize-links.qr.read
~~~

The Admin2 configuration workflow can also require:

~~~text
api.config.read
~~~

These permissions serve different purposes.

## Example Live Preview workflow

1. Open Goosialize Links in Admin2.
2. Change theme from Light to Dark.
3. Change accent from Yellow to Purple.
4. Observe the restricted unsaved Live Preview.
5. Save.
6. Confirm the public page.
7. Open the public page in another tab.

Unsaved preview state is temporary and session-scoped.

## Example link editing workflow

1. Keep the existing link ID unchanged.
2. Update the URL.
3. Update the Page-owned visible title if needed.
4. Save.
5. Test the tracked redirect.
6. Confirm analytics remain attached to the same link identity.

## Example action editing workflow

1. Keep the existing action ID unchanged.
2. Update the destination/contact value.
3. Update the Page-owned action label if needed.
4. Save.
5. Test the tracked action.
6. Confirm aggregate analytics continuity.

## Example upgrade preservation set

Before replacing plugin files, preserve:

~~~text
user/config/plugins/goosialize-links.yaml
user/pages/
user/media/goosialize-links/
user/data/goosialize-links/
~~~

The package itself can then be replaced under:

~~~text
user/plugins/goosialize-links/
~~~

## Example full-data review before uninstall

Review these Goosialize Links-owned areas:

~~~text
user/config/plugins/goosialize-links.yaml
user/data/goosialize-links/
user/media/goosialize-links/
~~~

Then separately identify the exact Goosialize Links Page under `user/pages/`.

Do not delete the whole Pages tree.

## Example troubleshooting sequence

If the public page fails:

1. verify `enabled: true`;
2. verify the route;
3. inspect the physical Page;
4. clear Grav cache;
5. inspect logs;
6. verify the plugin dependencies.

If analytics fail:

1. inspect the analytics directory;
2. inspect `.analytics.lock`;
3. inspect the daily `.events` file;
4. verify mode `0640`;
5. inspect fail-open analytics log entries.

## Unsafe examples

Do not configure unsupported web schemes such as arbitrary executable or script
schemes.

Do not manually create request-controlled redirect destinations.

Do not manually replace generated link/action identities without a specific
migration reason.

Do not treat preview tokens as permanent public URLs.

## Related documentation

- [Quick Start](QUICK_START.md)
- [Configuration Reference](CONFIGURATION.md)
- [Actions](ACTIONS.md)
- [Links](LINKS.md)
- [Appearance](APPEARANCE.md)
- [Multilingual Content](MULTILINGUAL.md)
- [Analytics](ANALYTICS.md)
- [QR Code](QR_CODE.md)
- [Permissions](PERMISSIONS.md)
- [Troubleshooting](TROUBLESHOOTING.md)
