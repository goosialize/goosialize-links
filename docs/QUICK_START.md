# Quick Start

This guide takes Goosialize Links from installation to a working public Link in
Bio page.

## Requirements

Goosialize Links requires:

- Grav CMS 2.0.12 or newer
- Admin2 2.0.15 or newer
- Grav API plugin 1.0.12 or newer
- PHP 8.3 or newer

See [Compatibility](COMPATIBILITY.md) for the complete compatibility reference.

## 1. Install the plugin

Install Goosialize Links through the supported Grav package workflow or from a
verified release package.

The plugin directory must be:

~~~text
user/plugins/goosialize-links/
~~~

Release packages include the required production Composer dependencies.

See [Installation](INSTALLATION.md).

## 2. Enable the plugin

Default configuration:

~~~yaml
enabled: true
route: /bio
~~~

The default public route is:

~~~text
/bio
~~~

## 3. Open the Admin2 editor

Open Goosialize Links from the Admin2 Plugins area.

The editor contains:

- Live Preview
- Profile
- Appearance
- Social and contact actions
- Links

The configuration inputs use native Admin2 form controls.

## 4. Configure the profile

Shared plugin configuration controls the profile image and primary website URL.

Profile images are stored under:

~~~text
user/media/goosialize-links/profile/
~~~

Public profile name, title, description, link titles, and action labels are
managed through the Goosialize Links Page under Grav Pages.

## 5. Choose an appearance

Themes:

- Light
- Dark
- Sunrise

Accent colors:

- Yellow
- Blue
- Coral
- Green
- Purple

Button shapes:

- Square
- Rounded
- Pill

The public Goosialize Links credit is enabled by default and can be disabled.

## 6. Add actions

Supported action types are:

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

Each action has a stable internal identity used by tracking and analytics.

## 7. Add links

Each link supports:

- enabled or disabled state
- HTTP/HTTPS destination URL
- new-tab behavior
- stable internal identity

Link display titles are Page content and may be localized.

## 8. Use Live Preview

Live Preview renders the real public Goosialize Links template.

Supported shared settings can be previewed before Save. Unsaved preview state is
temporary and does not replace stored configuration.

See [Live Preview](LIVE_PREVIEW.md).

## 9. Save and verify

Save the Admin2 form and open the public page.

With the default route:

~~~text
https://example.com/bio
~~~

Replace `example.com` with the actual site hostname.

## 10. QR and analytics

Goosialize Links includes one primary QR code and aggregate first-party
analytics.

The current event categories are:

- `page_view`
- `link_click`
- `action_click`
- `qr_visit`

The FREE analytics model does not create visitor profiles or unique-user
tracking.

Continue with:

- [Admin Guide](ADMIN_GUIDE.md)
- [Configuration Reference](CONFIGURATION.md)
- [QR Code](QR_CODE.md)
- [Analytics](ANALYTICS.md)
