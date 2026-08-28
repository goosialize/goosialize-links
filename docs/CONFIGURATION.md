# Configuration Reference

This document describes the current Goosialize Links configuration contract.

Shared plugin configuration is stored in:

~~~text
user/config/plugins/goosialize-links.yaml
~~~

Public textual content is managed through the Goosialize Links Page under Grav
Pages.

## Default configuration

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

## `enabled`

Type: boolean

Default:

~~~yaml
enabled: true
~~~

Controls whether the plugin runtime is enabled.

## `route`

Type: local Grav route

Default:

~~~yaml
route: /bio
~~~

The route is hidden from the standard Admin2 plugin form.

The route normalizer:

- adds a leading slash where required;
- rejects unsafe route values;
- keeps the route local to the Grav site.

Normal plugin boot does not automatically create or rewrite Page files.

An existing physical Goosialize Links Page may participate in effective route
resolution.

If physical Page lookup fails, the plugin keeps the normalized configured route
instead of taking down unrelated frontend pages.

## `profile`

### `profile.image`

Admin2 field type: file

Destination:

~~~text
user://media/goosialize-links/profile
~~~

Rules:

- maximum one file;
- JPEG, PNG, or WebP;
- randomized stored filename;
- overwrite avoidance enabled.

The profile image is shared across language versions.

### `profile.website_url`

Admin2 field type: URL

Optional shared primary website destination.

The normalized destination must use HTTP or HTTPS.

### `profile.name`

### `profile.title`

### `profile.description`

These keys remain part of the normalized configuration contract for
compatibility and fallback behavior.

Current editable public profile text is managed through the Goosialize Links
Page.

### `profile.translations`

Hidden compatibility structure for localized profile content.

Current user-facing localization is Page-centered.

## `appearance`

### `appearance.theme`

Admin2 field type: select

Default:

~~~yaml
theme: light
~~~

Allowed values:

- `light`
- `dark`
- `sunrise`

Unsupported values are rejected.

### `appearance.accent`

Admin2 field type: select

Default:

~~~yaml
accent: yellow
~~~

Allowed values:

- `yellow`
- `blue`
- `coral`
- `green`
- `purple`

### `appearance.button_shape`

Admin2 field type: select

Default:

~~~yaml
button_shape: rounded
~~~

Allowed values:

- `square`
- `rounded`
- `pill`

### `appearance.powered_by`

Admin2 field type: toggle

Default:

~~~yaml
powered_by: true
~~~

Controls visibility of the public Goosialize Links credit.

For compatibility with configurations created before this key existed, a
missing value behaves as enabled.

## `actions`

Admin2 field type: list

Each action contains a stable internal identity and shared behavior fields.

### `.id`

Hidden durable identity.

Do not manually replace generated IDs.

Analytics use the stable identity so counts remain associated with the same
action after editing or reordering.

### `.enabled`

Type: boolean

Default for a new action:

~~~yaml
enabled: true
~~~

Controls whether the action is rendered publicly.

### `.type`

Supported values:

- `website`
- `instagram`
- `facebook`
- `tiktok`
- `youtube`
- `linkedin`
- `x`
- `email`
- `phone`
- `whatsapp`

The default action type is `website`.

### `.value`

The destination or contact value.

Normalization depends on the selected action type.

Web and social actions use validated HTTP/HTTPS destinations.

Email actions normalize to an email destination.

Phone actions normalize to a `tel:` destination.

WhatsApp values normalize to a `https://wa.me/` destination.

Unsafe or malformed values are rejected.

### `.label`

The current public action label is Page content.

### `.translations`

Hidden compatibility structure for localized labels.

## `links`

Admin2 field type: list

### `.id`

Hidden durable link identity.

The ID is used by tracking and analytics and should not be manually changed.

### `.enabled`

Type: boolean

Default for a new link:

~~~yaml
enabled: true
~~~

Controls whether the link is rendered publicly.

### `.url`

Admin2 field type: URL

Supported web schemes are HTTP and HTTPS.

Unsafe or unsupported URL schemes are rejected.

### `.new_tab`

Type: boolean

Controls whether the public destination opens in a new browser tab.

The default for a new link is enabled.

### `.title`

The current public link title is Page content.

### `.translations`

Hidden compatibility structure for localized link titles.

## Public Page content

The Goosialize Links Page blueprint exposes the current editable public text
fields:

- Profile name
- Profile title
- Profile description
- Link titles
- Action labels

This separates localized editorial content from shared plugin infrastructure
configuration.

## Live Preview state

The plugin configuration editor provides a restricted Live Preview.

Unsaved preview state:

- is temporary;
- is session-scoped;
- uses a random token;
- accepts only an explicit whitelist of shared fields;
- does not mutate the saved configuration.

The current preview whitelist includes supported values from:

- profile website URL;
- appearance theme;
- appearance accent;
- appearance button shape;
- action shared fields;
- link shared fields.

See [Live Preview](LIVE_PREVIEW.md).

## Route safety

The configured route is not an arbitrary external URL.

Tracking and QR routes derive their behavior from validated plugin state rather
than accepting request-provided external redirect destinations.

## URL safety

User-configured web destinations are normalized and restricted to supported
schemes.

The current web URL boundary accepts:

~~~text
http
https
~~~

Email and telephone action types use their dedicated normalized destination
forms.

## Invalid values

The configuration normalizers fail closed on malformed or unsupported values.

Examples include:

- unsupported theme;
- unsupported accent;
- unsupported button shape;
- unsafe route;
- unsafe web URL;
- malformed action identity;
- malformed link identity;
- malformed language code;
- invalid Powered by boolean value.

Correct the configuration instead of bypassing the normalizers.

## Storage ownership

Configuration:

~~~text
user/config/plugins/goosialize-links.yaml
~~~

Profile media:

~~~text
user/media/goosialize-links/profile/
~~~

Analytics:

~~~text
user/data/goosialize-links/analytics/
~~~

See [Data Storage](DATA_STORAGE.md) for the complete storage model.

## Related documentation

- [Admin Guide](ADMIN_GUIDE.md)
- [Public Page](PUBLIC_PAGE.md)
- [Actions](ACTIONS.md)
- [Links](LINKS.md)
- [Appearance](APPEARANCE.md)
- [Multilingual Content](MULTILINGUAL.md)
- [Security](SECURITY.md)
