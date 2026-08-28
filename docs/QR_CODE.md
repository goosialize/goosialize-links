# QR Code

Goosialize Links includes one primary QR code for the active public Link in Bio
page.

The current FREE product contract uses one fixed QR identity.

## QR identity

The primary QR identity is:

~~~text
qr_primary
~~~

The current product does not expose multiple QR identities, campaign QR codes,
or QR write-management operations.

## Public tracked QR route

The tracked QR route is derived from the effective public Goosialize Links
route.

With the default public route:

~~~text
/bio
~~~

the tracked QR path uses the primary QR identity under that public route.

The tracked route records one QR visit and then redirects to the active public
page.

## Redirect behavior

The tracked QR redirect destination is derived from normalized plugin state.

The request cannot provide an arbitrary external redirect destination.

This prevents the QR route from becoming an open redirect.

## QR visit analytics

A successful tracked QR visit records:

~~~text
qr_visit
~~~

The event is associated with:

~~~text
qr_primary
~~~

The QR visit counter is part of the aggregate analytics model.

See [Analytics](ANALYTICS.md).

## QR image endpoints

Goosialize Links supports QR image generation in:

- PNG
- SVG

The QR image represents the tracked QR URL.

## Image requests do not count as visits

Loading or downloading the QR PNG or SVG does not record a `qr_visit`.

A QR visit is recorded only when the tracked QR route itself is requested.

This prevents Admin2 previews and downloads from inflating visit counts.

## Admin2 QR surface

The current Admin2 QR surface provides:

- QR preview information;
- tracked QR URL;
- PNG download;
- SVG download;
- aggregate QR visit count.

It is a read/download surface.

## Admin API routes

The plugin exposes protected administration endpoints for QR information and
downloads.

The current administration contract includes routes for:

- QR data;
- dashboard data;
- PNG download;
- SVG download.

These routes are separate from the public tracked QR route.

## Permission

The dedicated Goosialize Links permission is:

~~~text
api.goosialize-links.qr.read
~~~

This permission controls the current QR/analytics read surface.

No QR write permission is defined in the current FREE contract.

See [Permissions](PERMISSIONS.md).

## PNG download

The protected PNG download returns the primary QR image in PNG format.

The response uses defensive image headers and a fixed controlled filename.

The filename is based on:

~~~text
goosialize-links-qr.png
~~~

## SVG download

The protected SVG download returns the primary QR image in SVG format.

The response uses defensive headers and a fixed controlled filename.

The filename is based on:

~~~text
goosialize-links-qr.svg
~~~

## Destination construction

The QR destination is built from the effective public route and the current
site origin.

Supported public URL schemes are restricted to:

~~~text
http
https
~~~

The QR generator does not accept an arbitrary unsupported URL scheme.

## Multilingual behavior

The public QR flow resolves to the active effective public page.

The QR identity itself remains:

~~~text
qr_primary
~~~

The current FREE product does not create separate analytics identities for each
translated public label or language.

## Security boundary

The QR implementation preserves several boundaries:

- QR images do not themselves increment analytics;
- tracked QR redirects resolve only to the plugin's active public page;
- request-controlled external redirect targets are not trusted;
- administration endpoints require authenticated permission;
- the QR identity is fixed to the supported primary ID.

## Analytics storage

QR visits are written through the same aggregate analytics storage model as
other public events.

Current maintenance storage uses append-only daily event journals.

The QR event line is read back through the same analytics read model used by
Admin2 reporting.

Legacy daily YAML analytics remain readable.

## FREE product boundary

The current FREE QR feature includes:

- one primary QR code;
- PNG rendering/download;
- SVG rendering/download;
- one tracked redirect;
- aggregate QR visit counting.

It does not include:

- multiple QR codes;
- campaign-specific QR identities;
- custom QR destinations independent of the public page;
- QR styling/editor workflows;
- QR create/update/delete operations;
- advanced QR analytics.

## Verification checklist

Verify all of the following:

1. the QR PNG endpoint returns an image;
2. the QR SVG endpoint returns an image;
3. the image payload is valid for the requested format;
4. loading PNG does not increase QR visits;
5. loading SVG does not increase QR visits;
6. requesting the tracked QR route records one `qr_visit`;
7. the tracked route returns a temporary redirect;
8. the redirect location is the active public page;
9. an attempted request-controlled external destination is ignored/rejected;
10. an unauthorized Admin/API user cannot read QR administration data.

## Troubleshooting

If the QR image does not render:

1. verify the plugin package includes its production dependencies;
2. confirm PHP meets the required version;
3. confirm the public route is valid;
4. clear Grav cache;
5. inspect Grav logs.

If QR administration is unavailable:

1. confirm Admin2 and the Grav API plugin are installed;
2. verify the authenticated user has:

~~~text
api.goosialize-links.qr.read
~~~

3. confirm the plugin is enabled;
4. inspect the authenticated API response.

If QR visits do not increase:

1. make sure you are requesting the tracked QR route rather than the PNG/SVG
   image endpoint;
2. confirm analytics runtime storage is writable;
3. inspect Grav logs for fail-open analytics errors.

## Related documentation

- [Admin Guide](ADMIN_GUIDE.md)
- [Public Page](PUBLIC_PAGE.md)
- [Analytics](ANALYTICS.md)
- [Privacy](PRIVACY.md)
- [Permissions](PERMISSIONS.md)
- [Security](SECURITY.md)
