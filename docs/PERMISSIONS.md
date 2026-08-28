# Permissions

Goosialize Links uses a dedicated plugin permission for its current protected
QR and analytics administration surface.

## Dedicated permission

The current Goosialize Links permission is:

~~~text
api.goosialize-links.qr.read
~~~

This permission is defined by the plugin and is separate from generic package
management access.

## What the permission controls

The dedicated read permission protects the current Admin2/API QR and analytics
read surface.

It is used for access to:

- Goosialize Links QR administration;
- dashboard data;
- QR metadata;
- PNG download;
- SVG download;
- aggregate analytics data exposed through the protected administration
  surface.

## Read-only contract

The current QR administration feature is read/download oriented.

There is no Goosialize Links QR write permission in the current FREE product
contract.

No permission is currently defined for:

- creating QR identities;
- editing QR identities;
- deleting QR identities;
- changing QR destinations independently;
- managing QR campaigns;
- QR styling writes.

## Fixed QR identity

The current supported QR identity is:

~~~text
qr_primary
~~~

Because the FREE product exposes one fixed primary QR, the administration
contract does not need a QR create/update/delete permission.

## Admin2 configuration access

The dedicated Goosialize Links permission does not replace the normal platform
permissions that Admin2 itself requires to load and edit plugin configuration.

The installed Admin2 configuration workflow also depends on the platform
configuration-read capability used by Admin2.

The relevant platform permission is:

~~~text
api.config.read
~~~

These permissions have different responsibilities.

## Permission responsibilities

### `api.goosialize-links.qr.read`

Controls the Goosialize Links protected QR/analytics read surface.

### `api.config.read`

Platform/Admin2 prerequisite used when loading plugin configuration and its
blueprint.

Granting one should not be treated as implicitly granting the other.

## Superusers

Grav superusers inherit the access required by the administration workflow.

For non-superuser accounts, permissions should be granted explicitly according
to the user's role.

## Least privilege

For staff who only need to view QR/analytics information, grant only the
permissions required for that role.

Do not grant broad administrative or package-management access merely to make
the Goosialize Links page work.

## Public routes

Public Link in Bio routes do not require the QR administration permission.

Public visitors can access the configured public experience without
administrative API rights.

This includes normal public interactions such as:

- viewing the Link in Bio page;
- following a tracked link;
- following a tracked action;
- following the tracked primary QR route.

## Admin API behavior

Protected administration endpoints validate the authenticated user before
returning protected data.

Unauthorized users must not receive the protected QR/analytics payload.

The release acceptance suite verifies both unauthorized and authorized API
behavior.

## Live Preview

Live Preview belongs to the protected Admin2 editor workflow.

Its preview state is session-backed and uses a random token.

The token is not a substitute for user authorization.

The preview API remains inside the authenticated administration boundary.

## No `api.access` dependency

Goosialize Links uses its dedicated plugin permission for the current QR read
surface rather than relying on a broad generic API-access permission as the
product-specific authorization contract.

## Permission definition

The plugin permission hierarchy is registered from:

~~~text
permissions.yaml
~~~

The current permission path is:

~~~text
api
└── goosialize-links
    └── qr
        └── read
~~~

which resolves to:

~~~text
api.goosialize-links.qr.read
~~~

## Recommended role setup

For a non-superuser who needs Goosialize Links administration:

1. ensure the account can access the required Admin2 configuration workflow;
2. grant the Goosialize Links QR read permission when QR/analytics access is
   required;
3. avoid unrelated permissions unless the user's job requires them;
4. verify access using the actual non-superuser account.

## Verification checklist

Verify all of the following:

1. a superuser can open the Goosialize Links administration surface;
2. an authorized non-superuser can read protected QR/dashboard data;
3. an unauthorized user is denied protected QR/dashboard data;
4. PNG download respects the permission boundary;
5. SVG download respects the permission boundary;
6. the public `/bio` experience remains publicly accessible as configured;
7. no QR write permission appears in the plugin permission tree.

## Troubleshooting

If a user can open Admin2 but cannot access Goosialize Links QR/analytics data:

1. confirm the user is authenticated;
2. confirm the dedicated permission is granted:

~~~text
api.goosialize-links.qr.read
~~~

3. confirm Admin2 has the platform configuration-read access it requires;
4. clear Grav cache after permission changes;
5. sign out and back in if necessary;
6. retry with the exact user account;
7. inspect the protected API response and Grav logs.

If a user has broader access than intended, review the complete Grav role and
group permission tree rather than only the Goosialize Links permission.

## Related documentation

- [Admin Guide](ADMIN_GUIDE.md)
- [QR Code](QR_CODE.md)
- [Analytics](ANALYTICS.md)
- [Security](SECURITY.md)
- [Privacy](PRIVACY.md)
