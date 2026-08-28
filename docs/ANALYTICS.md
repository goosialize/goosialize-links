# Analytics

Goosialize Links includes privacy-preserving aggregate first-party analytics.

The analytics model is intentionally limited to product-level interaction
counts. It does not build visitor profiles.

## Event categories

The current analytics event types are:

~~~text
page_view
link_click
action_click
qr_visit
~~~

No other public analytics event types are part of the current FREE contract.

## Page views

A successful public Goosialize Links page request can record:

~~~text
page_view
~~~

Page-view analytics are aggregate counts.

## Link clicks

A successful tracked link redirect records:

~~~text
link_click
~~~

The event is associated with the link's stable internal identity.

Changing or translating the visible link title does not intentionally change
the analytics identity.

## Action clicks

A successful tracked action redirect records:

~~~text
action_click
~~~

The event is associated with the action's stable internal identity.

Changing or translating the visible action label does not intentionally change
the analytics identity.

## QR visits

A successful tracked primary QR route records:

~~~text
qr_visit
~~~

The supported QR identity is:

~~~text
qr_primary
~~~

Loading or downloading the PNG or SVG QR image itself does not record a QR
visit.

## Aggregate reporting

The Admin2 analytics read model aggregates:

- total page views;
- total clicks;
- total QR visits;
- per-link click counts;
- per-action click counts;
- primary QR visit counts.

Total clicks combine link clicks and action clicks.

## Stable identities

Links and actions use durable internal IDs.

Analytics are keyed to those IDs rather than visible translated labels.

This allows display content to change while aggregate counts remain attached to
the same logical item.

## Storage directory

Analytics runtime data is stored outside the plugin package under:

~~~text
user/data/goosialize-links/analytics/
~~~

Plugin updates do not replace this runtime data.

## Current maintenance storage model

Current maintenance code uses an append-only daily event journal.

The journal filename format is:

~~~text
YYYY-MM-DD.events
~~~

For example:

~~~text
2026-08-28.events
~~~

Each public analytics hit appends one small event record.

## Public write path

The current public write path does not:

- parse the complete daily YAML aggregate;
- dump a complete YAML aggregate;
- atomically rewrite the full daily aggregate;
- call `fsync()` for every public hit.

The public event path performs a small append under the analytics write lock.

## Journal locking

Public journal writes use an exclusive analytics lock.

The read model uses a shared analytics lock while reading the daily baseline
and pending journal.

This protects concurrent reads and writes without restoring the old full-file
rewrite-per-hit behavior.

## Journal permissions

Daily `.events` files use mode:

~~~text
0640
~~~

The analytics lock file also uses restricted permissions.

## Legacy YAML compatibility

Existing analytics YAML remains readable.

Legacy daily files use the format:

~~~text
YYYY-MM-DD.yaml
~~~

The current read model can combine:

- an existing daily YAML baseline;
- pending events from the matching `.events` journal.

This allows the maintenance storage change to remain backward compatible.

## YAML and journal merge

If both files exist for the same date:

~~~text
YYYY-MM-DD.yaml
YYYY-MM-DD.events
~~~

the read model loads the baseline and applies the journal events on top of it.

The legacy YAML file is not rewritten merely because new public events are
recorded.

## Journal-only days

A date does not require a YAML baseline.

A day containing only:

~~~text
YYYY-MM-DD.events
~~~

is still discovered and included in analytics reporting.

## Date discovery

Analytics date discovery considers both:

~~~text
*.yaml
*.events
~~~

Dates are deduplicated before aggregation.

This prevents a baseline+journal pair for the same date from being counted as
two separate days.

## Journal event structure

The current journal stores one event per line.

Each event carries the minimum information required by the aggregate model:

- event timestamp;
- event type;
- stable link/action/QR identity when required.

The journal does not require a visitor identifier.

## No visitor identifier

The FREE analytics storage model does not create or store a persistent visitor
identity for analytics reporting.

There is no analytics visitor profile in the current contract.

## Privacy exclusions

The current FREE analytics model does not provide analytics based on:

- IP address;
- HTTP user agent;
- browser;
- device;
- fingerprint;
- GeoIP;
- country;
- referrer;
- UTM source;
- UTM medium;
- UTM campaign;
- unique visitors;
- funnels;
- campaign profiles.

See [Privacy](PRIVACY.md).

## No external analytics service

The current aggregate storage model does not require:

- Redis;
- an external analytics database;
- a SaaS analytics provider;
- a remote tracking service.

Analytics are stored locally in the Grav installation.

## Fail-open public behavior

Analytics recording is non-critical to the public visitor flow.

If an analytics write fails, Goosialize Links catches the analytics error and
logs the failure.

The analytics failure does not intentionally take down:

- the public page;
- a tracked link redirect;
- a tracked action redirect;
- the QR redirect flow.

The public experience remains primary.

## Malformed analytics data

Malformed analytics journal data fails locally when the affected analytics file
is read.

Invalid analytics storage should not be silently reinterpreted as valid data.

Reporting can skip or isolate invalid analytics days according to the calling
read-model behavior.

## High-volume behavior

The maintenance journal design has been verified with high-volume recording.

The release test contract verifies:

- thousands of journal events can be recorded;
- no daily YAML is created by the hit path;
- event counts remain exact;
- journal line counts remain exact.

HTTP acceptance also verifies concurrent public traffic across:

- page views;
- link clicks;
- action clicks;
- QR visits.

## Verified high-volume acceptance

The current remediation acceptance used:

~~~text
500 page views
120 link clicks
80 action clicks
60 QR visits
~~~

Expected total journal events:

~~~text
760
~~~

The read model returned exact totals for all four event categories.

This is verification evidence for the maintenance implementation, not a product
traffic limit.

## Daily journal scope

The journal is naturally bounded by date because each day uses a separate
`.events` file.

Current maintenance code does not introduce an external compaction service.

Legacy YAML support remains available to the read model.

## Analytics dashboard

The Admin2 dashboard uses the aggregate read model rather than reading only
legacy YAML files.

This means journal-only dates are visible to the current reporting surface.

## QR dashboard

QR totals use the same analytics date-discovery/read model.

Journal-only QR visits remain visible.

## Multilingual analytics

Translations do not create separate link or action identities.

The same stable identity is used across languages.

This preserves aggregate continuity when visible labels change by locale.

## Data retention

Goosialize Links does not automatically delete analytics data when the plugin
package is removed.

Site operators remain responsible for their own retention policy and lawful
basis.

See:

- [Privacy](PRIVACY.md)
- [Data Storage](DATA_STORAGE.md)
- [Uninstall and Data Retention](UNINSTALL_DATA_RETENTION.md)

## FREE analytics boundary

The current FREE analytics model includes:

- page-view counts;
- link-click counts;
- action-click counts;
- QR-visit counts;
- aggregate totals;
- stable per-link/per-action counters.

It does not include:

- unique-user analytics;
- visitor timelines;
- campaign analytics;
- conversion funnels;
- attribution reporting;
- device/browser reporting;
- country reporting;
- scheduled analytics email reports.

## Troubleshooting

If analytics do not increase:

1. confirm the public event actually occurred;
2. confirm the analytics directory is writable;
3. inspect Grav logs for fail-open analytics errors;
4. verify `.events` files are being created;
5. confirm the event journal has mode `0640`;
6. verify the analytics read model can discover the journal date.

If old data disappears after an update:

1. confirm the legacy `.yaml` files still exist;
2. confirm the corresponding date is valid;
3. verify the current read model can read that schema version;
4. do not manually delete the baseline while troubleshooting.

If QR visits appear lower than expected, remember that loading the PNG/SVG image
does not count as a QR visit.

## Related documentation

- [QR Code](QR_CODE.md)
- [Privacy](PRIVACY.md)
- [Security](SECURITY.md)
- [Data Storage](DATA_STORAGE.md)
- [Admin Guide](ADMIN_GUIDE.md)
- [Links](LINKS.md)
- [Actions](ACTIONS.md)
