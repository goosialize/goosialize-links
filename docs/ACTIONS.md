# Actions

Goosialize Links supports social and contact actions that can be displayed on
the public Link in Bio page.

Actions are managed from the Admin2 plugin editor.

## Supported action types

The current supported types are:

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

## Action structure

Each action contains:

- a stable internal ID;
- enabled state;
- action type;
- destination/contact value;
- Page-owned public label.

The stable ID is hidden from normal editors.

## Stable identity

Each action uses a durable internal identity.

The identity is used by:

- tracked action routes;
- aggregate analytics;
- multilingual display-label mapping.

Changing the public action label does not change the action identity.

Reordering actions also does not change the identity.

Do not manually rewrite generated action IDs.

## Enabled state

Each action can be enabled or disabled.

Disabled actions are not rendered publicly.

New actions default to enabled.

## Website

Type:

~~~text
website
~~~

Value:

~~~text
https://example.com
~~~

The destination must pass the normal HTTP/HTTPS URL validation boundary.

## Instagram

Type:

~~~text
instagram
~~~

Use a complete supported Instagram web destination.

Example:

~~~text
https://instagram.com/example
~~~

## Facebook

Type:

~~~text
facebook
~~~

Use a complete supported Facebook web destination.

## TikTok

Type:

~~~text
tiktok
~~~

Use a complete supported TikTok web destination.

## YouTube

Type:

~~~text
youtube
~~~

Use a complete supported YouTube web destination.

## LinkedIn

Type:

~~~text
linkedin
~~~

Use a complete supported LinkedIn web destination.

## X

Type:

~~~text
x
~~~

Use a complete supported X web destination.

## Email

Type:

~~~text
email
~~~

Value example:

~~~text
hello@example.com
~~~

Email actions are normalized to an email destination.

The public destination uses the appropriate email scheme.

Email actions do not open in a new browser tab by default.

## Phone

Type:

~~~text
phone
~~~

Value example:

~~~text
+35799123456
~~~

Phone values are normalized and rendered using:

~~~text
tel:
~~~

Phone actions do not open in a new browser tab by default.

Malformed telephone values are rejected.

## WhatsApp

Type:

~~~text
whatsapp
~~~

The configured phone value is normalized to digits and converted to a WhatsApp
destination using:

~~~text
https://wa.me/
~~~

Malformed WhatsApp numbers are rejected.

## Action labels

The public action label is Page content.

It is managed through the Goosialize Links Page rather than as the primary
shared plugin configuration field.

This allows action labels to participate in Grav multilingual Page workflows.

## Multilingual labels

Localized action labels can vary by language while preserving the same stable
action identity.

This means analytics remain associated with the same logical action even when
the visible label changes between languages.

See [Multilingual Content](MULTILINGUAL.md).

## Tracked action route

Public action clicks use an internal tracking route.

The tracking resolver identifies the action from its stable ID.

The destination is taken from normalized saved plugin state.

The request cannot supply an arbitrary external redirect destination.

Unknown or unsafe tracking paths are rejected.

## Analytics

A successful tracked action click records:

~~~text
action_click
~~~

The aggregate counter is attached to the stable action identity.

The FREE analytics model records counts only. It does not create visitor
profiles.

See [Analytics](ANALYTICS.md).

## URL safety

Web-based action types are restricted to supported web schemes.

The current web scheme boundary is:

~~~text
http
https
~~~

Unsafe schemes are rejected.

Email and phone actions use their dedicated normalization rules rather than
generic web URL handling.

## New-tab behavior

Action new-tab behavior is derived from the normalized action type.

Email and phone actions are not treated like normal browser-tab web links.

Other supported web/social actions may open in a new tab according to the
normalized public view model.

## Editing actions safely

When editing an existing action:

1. keep its generated ID unchanged;
2. update the type/value as required;
3. update the public label through Page content;
4. Save the Admin2 form;
5. verify the public action;
6. verify analytics still attach to the same identity.

## Reordering actions

Actions may be reordered in the Admin2 list.

Reordering does not intentionally create a new analytics identity.

The stable ID is what preserves continuity.

## Deleting an action

Removing an action removes it from the active public configuration.

Historical aggregate analytics files may still contain counts associated with
the old stable identity.

Deletion of the configured action does not imply deletion of historical
analytics storage.

## Live Preview

Shared action fields can participate in restricted unsaved Live Preview state.

Preview support does not bypass normal validation and does not save the action
automatically.

Public action labels remain Page content.

See [Live Preview](LIVE_PREVIEW.md).

## Troubleshooting

If an action does not appear:

1. confirm it is enabled;
2. confirm the action type is supported;
3. validate the configured value;
4. Save the plugin configuration;
5. confirm the Page-owned action label exists where required;
6. clear Grav cache;
7. inspect logs for normalization errors.

If the action appears but does not redirect:

1. verify the saved destination;
2. verify the action identity was not manually changed;
3. confirm the tracking path is using a known action ID;
4. check for URL validation failures.

## Related documentation

- [Configuration Reference](CONFIGURATION.md)
- [Admin Guide](ADMIN_GUIDE.md)
- [Public Page](PUBLIC_PAGE.md)
- [Live Preview](LIVE_PREVIEW.md)
- [Multilingual Content](MULTILINGUAL.md)
- [Analytics](ANALYTICS.md)
- [Security](SECURITY.md)
