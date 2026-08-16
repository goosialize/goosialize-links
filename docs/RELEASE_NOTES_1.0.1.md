# Goosialize Links 1.0.1

Goosialize Links FREE 1.0.1 is a packaging-only maintenance release. It keeps
the public and Admin2 behavior of 1.0.0 while making the released Git source
directly installable by Grav GPM.

## Compatibility change

- Includes the exact production Composer dependencies from the unchanged lock
  file so `vendor/autoload.php` is available from tagged source.
- Adds a clean Grav 2.0.12 tag-source installation gate that does not run
  Composer during installation.
- Verifies committed-vendor safety, source/package parity, and deterministic
  package generation.

## Runtime behavior

No PHP, JavaScript, CSS, Twig, route, schema, analytics, QR, or migration
behavior changes are included. Business Information and all other 1.1 work
remain excluded.

## Release contract

- Tag/title: `1.0.1`
- Asset: `goosialize-links-1.0.1.zip`
- Checksum sidecar: `goosialize-links-1.0.1.zip.sha256`
- Source: <https://github.com/goosialize/goosialize-links>

Grav GPM availability is not claimed until the separate submission is
accepted.
