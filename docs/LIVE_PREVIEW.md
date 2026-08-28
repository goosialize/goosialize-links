# Live Preview

Goosialize Links includes a restricted Admin2 Live Preview that renders the real
public Goosialize Links template.

The preview is designed to provide useful before-Save feedback without replacing
Admin2 behavior or allowing arbitrary unsaved configuration to reach the public
renderer.

## Where it appears

The Live Preview is part of the Goosialize Links Admin2 plugin editor.

The editor workspace keeps:

- Live Preview on the left on larger screens;
- Profile and Appearance alongside it;
- Actions and Links in the full-width editor flow;
- a normal stacked layout on smaller screens.

The preview is not sticky.

## Real public rendering

The preview uses the same public rendering path and template as the saved public
page.

Its preview URL includes the internal preview flag:

~~~text
?goosialize-links-preview=1
~~~

This makes the preview representative of the actual public experience rather
than a separate mock renderer.

## Preview session

A preview session uses a random 32-character hexadecimal token generated
server-side.

The token is associated with session-backed preview state.

Preview state is:

- temporary;
- session-scoped;
- restricted to the current editor workflow;
- not persisted as plugin configuration.

## Unsaved preview state

The editor can send a restricted draft overlay for selected shared settings.

The draft overlay is validated before being applied.

It does not replace the saved configuration.

## Supported preview fields

The current preview contract accepts an explicit whitelist of supported shared
fields.

### Profile

- website URL

### Appearance

- theme
- accent
- button shape

### Actions

Supported shared action fields used by preview state include the action identity
and editable shared action values required by the preview renderer.

### Links

Supported shared link fields include:

- ID
- enabled state
- URL
- new-tab behavior

Public profile text, link titles, and action labels remain Page content.

## Appearance preview

The following values can be previewed before Save.

Themes:

- Light
- Dark
- Sunrise

Accents:

- Yellow
- Blue
- Coral
- Green
- Purple

Button shapes:

- Square
- Rounded
- Pill

## Language preview

When multiple Grav languages are available, the preview exposes language
selection using the supported Grav language codes and native language names.

The preview path is resolved for the selected language.

This allows localized Page content to be checked before opening the public page
manually.

## Refresh Preview

Use Refresh Preview to reload the current preview.

A refresh does not make unsaved state permanent.

## Open Public Page

Open Public Page navigates to the effective saved public route in a new browser
context.

It should be used to verify the stored public experience after Save.

## Save behavior

The preview observes the actual Admin2 Save state without replacing the global
network layer.

After a successful Save, the preview workflow:

- clears temporary preview state;
- returns to a ready state;
- refreshes collection labels;
- reapplies the selected language where appropriate.

The saved plugin configuration and Page content then remain authoritative.

## R2-D isolation contract

The Live Preview integration is deliberately scoped to the Goosialize Links
editor.

It does not monkey-patch:

~~~text
window.fetch
~~~

It does not install document-wide input/change listeners.

It does not observe:

~~~text
document.body
~~~

for subtree mutations.

It does not run a body-wide text-node TreeWalker.

It does not use a global:

~~~text
document.querySelector(...)
~~~

lookup for preview behavior.

## Scoped editor behavior

Collection-label rewriting is limited to the Goosialize Links editor root.

Realtime preview input/change handling is also attached only to that editor
root.

The save-state observer watches the actual Save button state rather than the
entire Admin2 document.

These boundaries reduce the risk that Goosialize Links interferes with unrelated
Admin2 pages or components.

## Preview iframe

The real preview is displayed in a sandboxed iframe.

The current sandbox permits the capabilities required by the public template
and preview workflow while keeping the preview embedded inside the Admin2
workspace.

The preview viewport is intentionally narrow so the Link in Bio mobile
experience can be reviewed from the desktop editor.

## Security boundary

The preview API requires the same protected administration context used by the
Goosialize Links editor.

Preview payloads are validated against the explicit draft-field contract.

Unsupported preview fields are rejected.

Arbitrary preview routes are rejected.

Invalid preview tokens are rejected.

## What Live Preview does not do

Live Preview does not:

- save plugin configuration automatically;
- save Page content automatically;
- create public Page files;
- accept arbitrary configuration keys;
- expose arbitrary redirect destinations;
- replace Admin2's global request handling;
- convert native Admin2 controls into custom replacements.

## Profile images

Profile image uploads use the native Admin2 file workflow.

A newly selected image may require Save before the stored media path becomes
available to the normal public rendering path.

## Troubleshooting

If Live Preview does not load:

1. confirm Goosialize Links is enabled;
2. confirm Admin2 and the Grav API plugin meet minimum requirements;
3. verify the authenticated editor has the required access;
4. confirm the public route resolves;
5. clear Grav cache;
6. reload the editor;
7. inspect browser console and Grav logs.

If normal Admin2 controls work but realtime preview stops updating, use Refresh
Preview and verify the saved public page separately before changing any source
files.

## Related documentation

- [Admin Guide](ADMIN_GUIDE.md)
- [Public Page](PUBLIC_PAGE.md)
- [Configuration Reference](CONFIGURATION.md)
- [Multilingual Content](MULTILINGUAL.md)
- [Security](SECURITY.md)
- [Permissions](PERMISSIONS.md)
