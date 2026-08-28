# Appearance

Goosialize Links provides a bounded FREE appearance model for the public Link in
Bio page.

Appearance settings are managed through native Admin2 controls.

## Available settings

The current appearance contract includes:

- Theme
- Accent
- Button shape
- Powered by credit

No arbitrary custom CSS field is part of the current FREE appearance model.

## Theme

Configuration key:

~~~text
appearance.theme
~~~

Default:

~~~yaml
theme: light
~~~

Supported values:

- `light`
- `dark`
- `sunrise`

Unsupported theme values are rejected by normalization.

## Accent

Configuration key:

~~~text
appearance.accent
~~~

Default:

~~~yaml
accent: yellow
~~~

Supported values:

- `yellow`
- `blue`
- `coral`
- `green`
- `purple`

Unsupported accent values are rejected.

## Button shape

Configuration key:

~~~text
appearance.button_shape
~~~

Default:

~~~yaml
button_shape: rounded
~~~

Supported values:

- `square`
- `rounded`
- `pill`

Unsupported values are rejected.

## Powered by credit

Configuration key:

~~~text
appearance.powered_by
~~~

Default:

~~~yaml
powered_by: true
~~~

The public Goosialize Links credit is enabled by default.

It can be disabled:

~~~yaml
appearance:
  powered_by: false
~~~

When disabled, the public footer credit is not rendered.

## Backward compatibility

The Powered by option was introduced after the original stable configuration
contract.

If the key is missing, the normalizer treats it as enabled.

This preserves the existing public credit for older configurations unless the
site owner explicitly disables it.

## Public rendering

Appearance values are normalized before they enter the public view model.

The public template receives bounded values for:

- theme;
- accent;
- button shape;
- Powered by visibility.

The template exposes normalized appearance values through its public rendering
attributes and classes.

## Live Preview

Theme, accent, and button shape are included in the restricted Live Preview
draft contract.

This allows appearance changes to be viewed before Save.

Unsaved preview state is temporary and does not replace stored configuration.

The Powered by setting follows the normal saved configuration workflow.

See [Live Preview](LIVE_PREVIEW.md).

## Mobile-first presentation

Goosialize Links is designed as a mobile-first Link in Bio experience.

The Admin2 preview uses a narrow viewport so appearance changes can be reviewed
in a presentation closer to the expected mobile page.

The public page remains responsive outside the preview.

## Theme selection

Use Light when the page should have a bright neutral presentation.

Use Dark when the overall public experience should use the darker predefined
appearance.

Use Sunrise for the third predefined FREE theme.

Themes are intentionally bounded. The current FREE product does not expose an
arbitrary theme-definition editor.

## Accent selection

Accent controls the predefined visual accent used by the public design.

The available accents are deliberately limited to the supported product
palette.

Custom arbitrary color input is not part of the current FREE configuration
surface.

## Button shape selection

Button shape controls the predefined geometry of public links and actions.

Choose:

- Square for the least rounded treatment;
- Rounded for the default treatment;
- Pill for the most rounded treatment.

## Invalid configuration

If a stored appearance value does not match the supported contract, the bounded
appearance normalizer rejects the invalid value.

Do not add unsupported appearance values directly to configuration and expect
the public renderer to accept them.

## FREE product boundary

The current FREE appearance model is intentionally limited.

It does not include:

- arbitrary custom CSS;
- unrestricted theme creation;
- arbitrary color-palette editing;
- an advanced visual theme builder.

The available themes, accents, and button shapes are the supported bounded
appearance contract.

## Troubleshooting

If an appearance change does not show:

1. verify the selected value is supported;
2. Save the Admin2 plugin form;
3. Refresh Live Preview;
4. open the saved public page;
5. clear Grav cache if required;
6. inspect configuration for unsupported manually entered values.

If the Powered by credit remains visible, confirm:

~~~yaml
appearance:
  powered_by: false
~~~

is actually saved in the effective plugin configuration.

## Related documentation

- [Configuration Reference](CONFIGURATION.md)
- [Admin Guide](ADMIN_GUIDE.md)
- [Public Page](PUBLIC_PAGE.md)
- [Live Preview](LIVE_PREVIEW.md)
