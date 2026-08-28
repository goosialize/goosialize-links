# Links

Goosialize Links provides a managed list of public Link in Bio destinations.

Links are configured from the native Admin2 plugin editor.

## Link structure

Each link contains:

- a stable internal ID;
- enabled state;
- destination URL;
- new-tab behavior;
- Page-owned public title.

## Stable identity

Each link has a stable internal identity.

The identity is used by:

- tracked link routes;
- aggregate analytics;
- multilingual title mapping.

Changing the visible link title does not change the link identity.

Reordering links does not change the identity.

Do not manually rewrite generated link IDs.

## Enabled state

Each link can be enabled or disabled.

Disabled links are not rendered on the public page.

New links default to enabled.

## Destination URL

Each link has one destination URL.

Supported web schemes are:

~~~text
http
https
~~~

Unsafe or unsupported URL schemes are rejected by normalization.

Use complete URLs such as:

~~~text
https://example.com
~~~

## Public title

The visible link title is Page content.

It is edited through the Goosialize Links Page under Grav Pages.

This separates localized editorial content from the shared link destination.

## Multilingual titles

A link can display a different title in each supported Grav language while
keeping the same stable link identity.

This allows analytics to aggregate by the same logical link across languages.

See [Multilingual Content](MULTILINGUAL.md).

## New-tab behavior

Links include a boolean new-tab setting.

When enabled, the public link is rendered to open in a new browser tab.

The default for a new link is enabled.

The public renderer also applies the appropriate safe relationship attributes
for new-tab destinations.

## Tracked link route

Public link clicks use an internal tracked route.

The tracking resolver identifies the configured link from its stable ID.

The final redirect destination comes from normalized saved plugin state.

The request cannot provide an arbitrary external redirect destination.

Unknown tracking identities are rejected.

Unsafe tracking paths are rejected.

## Redirect safety

Tracked redirects are not open redirects.

The public request identifies a known internal link identity; it does not carry
an authoritative external destination.

The server resolves the saved normalized destination associated with that ID.

## Analytics

A successful tracked link click records:

~~~text
link_click
~~~

The count is associated with the link's stable identity.

Changing the visible title does not reset that identity.

The FREE analytics model stores aggregate counts rather than visitor profiles.

See [Analytics](ANALYTICS.md).

## Reordering links

Links can be reordered in the Admin2 list.

The stable ID preserves analytics continuity across reordering.

Reordering should not be used as a reason to regenerate link IDs.

## Editing a link safely

When editing an existing link:

1. leave its hidden ID unchanged;
2. update the destination URL if required;
3. update new-tab behavior if required;
4. edit the public title through Page content;
5. Save;
6. verify the tracked redirect;
7. verify analytics still attach to the same identity.

## Deleting a link

Removing a link removes it from the active public configuration.

Historical analytics data can still contain counters associated with the old
stable identity.

Removing a configured link does not automatically purge historical analytics
storage.

## Live Preview

Supported shared link fields can participate in restricted unsaved Live Preview
state.

The current preview contract can include:

- link ID;
- enabled state;
- URL;
- new-tab behavior.

Public titles remain Page content.

Preview state is temporary and does not save the link automatically.

See [Live Preview](LIVE_PREVIEW.md).

## Public rendering

Only enabled normalized links are exposed to the public view model.

The rendered public destination is escaped at the Twig boundary.

Invalid configuration is rejected before it becomes a public destination.

## Troubleshooting

If a link does not appear:

1. confirm the link is enabled;
2. confirm the URL uses HTTP or HTTPS;
3. confirm the Page-owned title exists where required;
4. Save the plugin configuration;
5. clear Grav cache;
6. reload the public page.

If a link appears but does not redirect:

1. verify the saved destination URL;
2. confirm the hidden ID has not been manually changed;
3. verify the tracked route uses a known link identity;
4. check Grav logs for normalization errors.

If analytics stop following a link after editing, confirm that the original
stable ID was preserved.

## Related documentation

- [Configuration Reference](CONFIGURATION.md)
- [Admin Guide](ADMIN_GUIDE.md)
- [Public Page](PUBLIC_PAGE.md)
- [Live Preview](LIVE_PREVIEW.md)
- [Multilingual Content](MULTILINGUAL.md)
- [Analytics](ANALYTICS.md)
- [Security](SECURITY.md)
