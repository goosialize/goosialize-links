# Privacy

Goosialize Links is designed around a privacy-preserving aggregate analytics
model.

The current FREE product records interaction counts without creating a visitor
profile.

## Analytics purpose

Analytics answer basic product questions such as:

- how many public page views occurred;
- how many times a link was clicked;
- how many times an action was clicked;
- how many tracked QR visits occurred.

The analytics model is not designed for behavioral profiling.

## Approved analytics events

The current analytics event categories are:

~~~text
page_view
link_click
action_click
qr_visit
~~~

No additional analytics event categories are part of the current FREE contract.

## No visitor profiles

Goosialize Links does not create an analytics visitor profile.

The aggregate model does not require a persistent visitor identifier.

Counts are associated with:

- the day;
- event type;
- stable link identity where required;
- stable action identity where required;
- the fixed primary QR identity where required.

## No IP analytics

The FREE analytics model does not provide analytics based on visitor IP
addresses.

IP addresses are not part of the approved analytics reporting model.

## No user-agent analytics

The FREE analytics model does not provide browser or device reporting from the
HTTP user agent.

It does not expose analytics such as:

- browser family;
- operating system;
- device category.

## No fingerprinting

Goosialize Links does not implement analytics fingerprinting in the current FREE
contract.

It does not build a synthetic visitor identity from browser/device signals.

## No GeoIP or country analytics

The current analytics model does not include GeoIP enrichment or country
reporting.

## No referrer analytics

The current FREE reporting model does not include referrer attribution.

## No UTM analytics

The current analytics model does not report:

- UTM source;
- UTM medium;
- UTM campaign.

Campaign attribution is outside the current FREE scope.

## No unique-user analytics

The current analytics model records aggregate event counts, not unique visitors.

The same person can therefore contribute more than one event count if they
perform the same action multiple times.

## No external analytics service

The current analytics implementation does not require an external tracking SaaS
provider.

Analytics storage remains local to the Grav installation.

The default runtime directory is:

~~~text
user/data/goosialize-links/analytics/
~~~

## Local storage

Current maintenance analytics use daily append-only journals.

Journal files follow:

~~~text
YYYY-MM-DD.events
~~~

Legacy daily YAML analytics remain readable for backward compatibility.

See [Analytics](ANALYTICS.md).

## Stable IDs are not visitor IDs

Links and actions use stable internal IDs.

These IDs identify configured product items.

They are not intended to identify people.

Examples:

- a link ID identifies a configured link;
- an action ID identifies a configured action;
- `qr_primary` identifies the primary QR code.

## Public redirects

Tracked link/action/QR routes use internal stable identities to resolve
normalized server-side destinations.

The request does not supply an authoritative arbitrary external redirect target.

This protects redirect safety without requiring visitor profiling.

## Profile content

The public Link in Bio profile can contain content entered by the site operator,
including:

- profile name;
- profile title;
- profile description;
- website URL;
- link titles;
- action labels.

Site operators are responsible for the personal or business information they
choose to publish.

## Uploaded profile image

The shared profile image is stored under:

~~~text
user/media/goosialize-links/profile/
~~~

The plugin does not automatically remove profile media when the plugin package
is removed.

## Admin2 preview session

Live Preview uses temporary session-backed preview state with a random token.

This token protects the temporary editor-preview workflow.

It is not used as an analytics visitor identifier.

Preview state is not part of the public analytics model.

## Admin access

QR and analytics administration requires authenticated access and the
appropriate permission.

The dedicated plugin permission is:

~~~text
api.goosialize-links.qr.read
~~~

See [Permissions](PERMISSIONS.md).

## Analytics failure behavior

Analytics are non-critical to the public visitor experience.

If analytics storage fails, the plugin logs the analytics failure and keeps the
public flow fail-open where possible.

This avoids making analytics availability a requirement for viewing the public
page or following a valid redirect.

## Data retention responsibility

Goosialize Links does not impose a universal analytics retention period.

Site operators remain responsible for:

- defining a retention policy;
- determining lawful basis where applicable;
- updating their privacy notice;
- securing backups;
- controlling administrative access;
- deleting data when required.

## Uninstall behavior

Removing or disabling the plugin does not automatically delete runtime data.

Potential retained data includes:

- plugin configuration;
- analytics;
- profile media;
- Page content.

See [Uninstall and Data Retention](UNINSTALL_DATA_RETENTION.md).

## Cookies

The current aggregate analytics contract described here does not depend on a
Goosialize Links analytics visitor cookie.

If the surrounding Grav site, theme, or other plugins use cookies, those remain
the responsibility of those components and the site operator.

## What Goosialize Links does not claim

The plugin does not by itself guarantee that a site is compliant with GDPR or
another privacy law.

Privacy compliance depends on the site's:

- jurisdiction;
- overall technology stack;
- legal basis;
- notices;
- retention practices;
- access controls;
- other installed plugins/services.

## Recommended site-operator actions

Site operators should:

1. document that basic aggregate Link in Bio interaction counts are collected;
2. define how long analytics files are retained;
3. restrict access to Admin2 analytics;
4. protect server backups;
5. review other site-level trackers separately;
6. delete data when their retention/legal requirements demand it.

## FREE privacy boundary

The current FREE product does not include:

- visitor profiles;
- unique-user reporting;
- browser analytics;
- device analytics;
- fingerprint analytics;
- country analytics;
- campaign attribution;
- referrer analytics;
- UTM analytics;
- behavioral funnels.

## Related documentation

- [Analytics](ANALYTICS.md)
- [Security](SECURITY.md)
- [Permissions](PERMISSIONS.md)
- [Data Storage](DATA_STORAGE.md)
- [Uninstall and Data Retention](UNINSTALL_DATA_RETENTION.md)
