# Multilingual Content

Goosialize Links integrates with Grav multilingual Page content while preserving
stable link and action identities across languages.

## Language source

The public renderer uses the active Grav language.

Goosialize Links reads the site's supported languages from Grav configuration
and uses the configured default language as the fallback reference.

## Public content ownership

Localized public text is Page content.

The Goosialize Links Page blueprint exposes:

- Profile name
- Profile title
- Profile description
- Link titles
- Action labels

This keeps editorial translations inside the Grav Page content model.

## Shared configuration

Some values are shared across languages.

Examples include:

- profile image;
- profile website URL;
- appearance settings;
- link destination URLs;
- action destination/contact values;
- stable link IDs;
- stable action IDs.

These values are not duplicated per translated display label.

## Profile translations

Profile text can vary by language.

Localized profile values include:

- name;
- title;
- description.

When a localized value is unavailable, the normalized fallback value is used.

## Link titles

Each logical link keeps one stable internal identity.

Its visible title can vary by language.

This allows, for example, one destination to display different translated
titles without creating separate analytics identities.

## Action labels

Each logical action keeps one stable internal identity.

Its visible label can vary by language.

The action destination remains associated with the same stable identity.

## Analytics identity

Translations do not create new analytics identities.

Aggregate analytics remain keyed to stable link and action IDs.

This means a link or action can be renamed or translated without losing
continuity of its aggregate counters.

## Language codes

Content language codes are normalized to lowercase.

The accepted language-code format supports normal Grav language identifiers
such as:

~~~text
en
el
en-gb
~~~

Malformed language codes are rejected.

Duplicate translation language entries are also rejected by normalization.

## Default language

Goosialize Links reads the Grav default language from:

~~~text
system.languages.default_lang
~~~

The default language participates in Page resolution and fallback behavior.

## Supported languages

The site's supported languages are read from:

~~~text
system.languages.supported
~~~

The plugin does not maintain an independent language registry.

## Default-language URL behavior

The effective public route follows the active Grav language and the site's
language-routing configuration.

When the active path already carries the current language prefix, the plugin
preserves the localized route behavior.

The plugin does not turn language selection into an external redirect.

## Physical Page files

Goosialize Links can resolve language-specific physical Page files.

A multilingual Page may use language-specific filenames such as:

~~~text
goosialize-links.en.md
goosialize-links.el.md
~~~

The exact physical structure follows Grav Page conventions and the site's
language setup.

## Page lookup

Physical Page discovery is bounded to the configured Goosialize Links route.

The plugin does not recursively scan unrelated Page trees for translated
Goosialize Links files on each frontend request.

## Missing translation behavior

If a localized value is unavailable, the renderer uses normalized fallback
content.

A missing translation should not create a new identity or duplicate the link or
action.

## Live Preview languages

Live Preview obtains the supported Grav languages from the current site.

Language labels use native language names where available.

The preview can switch the effective preview path to the selected language so
localized Page content can be checked before opening the public page.

## Profile image

The profile image is shared across languages.

It is stored outside translated Page files under:

~~~text
user/media/goosialize-links/profile/
~~~

Changing language does not require a separate profile image.

## Website URL

The profile website URL is also a shared configuration value.

If a site needs language-specific destination behavior, that should be handled
by the configured destination/site architecture rather than by inventing
unsupported per-language Goosialize Links configuration.

## Link destinations

Link URLs are shared configuration values.

Only the public title is localized in the current Page-centered content model.

## Action destinations

Action values are shared configuration values.

Only the public action label is localized in the current Page-centered content
model.

## Editing multilingual content

Recommended workflow:

1. configure supported Grav languages;
2. configure shared Goosialize Links settings;
3. open the Goosialize Links Page;
4. edit profile text for each required language;
5. edit link titles for each required language;
6. edit action labels for each required language;
7. Save;
8. verify each language through Live Preview;
9. open each public localized route;
10. confirm analytics identities remain unchanged.

## Identity safety

Do not create a second link or action solely because its visible label needs
translation.

Use the same stable item identity and edit the localized Page content.

This preserves aggregate analytics continuity.

## Troubleshooting

If a translation does not appear:

1. confirm the language is enabled in Grav;
2. confirm the active language is what you expect;
3. confirm the translated Goosialize Links Page exists;
4. verify the localized Page field has a value;
5. Save the Page;
6. clear Grav cache if necessary;
7. Refresh Live Preview;
8. open the localized public route directly.

If analytics appear split after translation changes, verify that link/action
stable IDs were not manually replaced.

## Related documentation

- [Public Page](PUBLIC_PAGE.md)
- [Admin Guide](ADMIN_GUIDE.md)
- [Live Preview](LIVE_PREVIEW.md)
- [Links](LINKS.md)
- [Actions](ACTIONS.md)
- [Configuration Reference](CONFIGURATION.md)
- [Analytics](ANALYTICS.md)
