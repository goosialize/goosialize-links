# Release Verification

This document describes the Goosialize Links release-verification model.

A release is not considered ready based only on successful source editing.

Verification covers source contracts, runtime behavior, package contents,
clean-install acceptance, protected APIs, and manual browser acceptance.

## Primary automated gate

The primary release test entry point is:

~~~text
tests/release/run-all.sh
~~~

The expected final automated marker is:

~~~text
AUTOMATED RELEASE GATES = PASS
~~~

## Supported release runtime

The automated release suite uses the minimum supported Grav baseline:

~~~text
Grav 2.0.12
~~~

This verifies the release against the declared minimum compatibility rather than
only a newer development runtime.

## Static contract

The static contract is executed through:

~~~text
tests/release/static-contract.sh
~~~

It verifies areas including:

- required package source;
- YAML/JSON metadata;
- plugin identity;
- compatibility metadata;
- locked production dependencies;
- committed vendor safety;
- FREE product boundary;
- Admin2 contract;
- multilingual contract;
- QR/API split;
- permissions;
- JavaScript syntax;
- privacy boundary;
- secret scanning;
- source diff hygiene.

## PHP syntax

Production PHP source is syntax-checked in the supported Grav/PHP runtime.

A release must not proceed with a PHP syntax failure.

## Package build

The release suite creates a deterministic distributable package from approved
source.

The package must have:

- one plugin root;
- required source files;
- required production dependencies;
- no development-only release content;
- no secret-sensitive package material.

## Deterministic archive

The same approved source state should produce the expected deterministic package
inventory and archive behavior.

The release output records a SHA-256 digest for the built package.

## Package-content contract

The package contract verifies:

- plugin root layout;
- required metadata;
- version/license;
- production `vendor/`;
- QR dependencies/classes;
- package secret scan;
- absence of development-only files;
- source/package vendor parity.

## Core runtime contract

The pure runtime contract is implemented in:

~~~text
tests/release/runtime-core.php
~~~

It verifies areas including:

- social/contact action normalization;
- safe destinations;
- appearance normalization;
- Powered by behavior;
- multilingual content;
- Page provisioning compatibility;
- bounded physical Page lookup;
- preview-state validation;
- profile-image resolution;
- analytics;
- tracking routes;
- QR generation.

## R2-A verification

The maintenance release contract verifies:

- normal plugin boot does not automatically provision Page files;
- physical Page resolution failure is handled locally;
- configured-route fallback remains available.

## R2-B verification

The maintenance release contract verifies:

- Page lookup is bounded to the configured route;
- deep unrelated Goosialize Links files are not recursively scanned;
- duplicate matching route directories are rejected;
- unsafe configured routes are rejected.

## R2-C verification

The maintenance release contract verifies:

- Powered by defaults enabled;
- explicit disable is accepted;
- serialized false values normalize correctly;
- missing key remains backward compatible.

## R2-D verification

The static/Admin2 contract verifies that Live Preview does not:

~~~text
replace window.fetch
observe document.body
install document-wide input/change listeners
perform body-wide preview text traversal
~~~

It also verifies the scoped editor layout and Save-state behavior.

## R2-E verification

The analytics maintenance contract verifies:

~~~text
YAML_DUMP=0
FSYNC=0
ATOMIC_WRITER=0
APPEND_MODE=1
LOCK_EX=1
LOCK_SH=1
JOURNAL_SUFFIX=1
~~~

The public analytics hit path therefore uses the append-only journal model.

## Analytics runtime verification

The runtime test verifies:

- hit path does not create daily YAML;
- `.events` journal is created;
- journal permissions are `0640`;
- page views record correctly;
- QR visits record correctly;
- link clicks record correctly;
- action clicks record correctly;
- legacy YAML and journal data merge correctly;
- journal-only dates are discovered;
- high-volume counts remain exact;
- malformed journal data fails locally.

## High-volume HTTP acceptance

The maintenance implementation has also been exercised through real HTTP traffic
in a clean Grav runtime.

The verified acceptance workload used:

~~~text
500 page views
120 link clicks
80 action clicks
60 QR visits
~~~

Expected event total:

~~~text
760
~~~

Observed read-model totals matched:

~~~text
500:120:80:60
~~~

This is release-verification evidence, not a declared maximum traffic limit.

## Clean Grav package install

The release suite installs the built package into a clean supported Grav
container.

It verifies:

- Grav runtime readiness;
- Admin/API setup;
- plugin configuration;
- public `/bio` HTTP success.

## QR clean-install contract

The package-installed runtime verifies:

- PNG returns successfully;
- SVG returns successfully;
- PNG payload is valid;
- SVG payload is valid;
- defensive headers are present;
- loading QR images does not record analytics.

## Tracked QR verification

The clean-install contract verifies:

- tracked QR returns a temporary redirect;
- redirect target is the active public page;
- request-controlled external target does not become authoritative;
- QR analytics increment.

## Link/action HTTP regression

The release suite verifies:

- tracked link returns the configured destination;
- tracked action returns the configured destination;
- link analytics increment;
- action analytics increment.

## Admin2/API permission acceptance

Protected administration verification covers:

- preview-state security;
- preview API;
- unauthorized API behavior;
- authorized API behavior;
- superuser API behavior.

Human browser verification remains required for full Admin2 UX acceptance.

## Package-installed dependencies

The final installed package is checked for required production dependencies.

The package must not depend on a development-only Composer install performed
after deployment.

## GPM-style tag-source installation

The release suite also tests the source shape used for Grav/GPM distribution.

Expected marker:

~~~text
GPM-STYLE TAG SOURCE INSTALL = PASS
~~~

This confirms the tracked source is installable with its committed production
dependencies.

## Manual browser acceptance

Automated release gates do not replace human browser verification.

Use:

~~~text
docs/MANUAL_BROWSER_ACCEPTANCE_CHECKLIST.md
~~~

The manual checklist covers:

- Admin2 layout;
- responsive editor behavior;
- Live Preview;
- Save persistence;
- Powered by;
- profile image;
- public content;
- links/actions;
- multilingual behavior;
- QR;
- analytics;
- permissions;
- browser console;
- responsive public page.

Expected final manual marker:

~~~text
GOOSIALIZE_LINKS_MANUAL_BROWSER_ACCEPTANCE=PASS
~~~

## Source diff hygiene

Before commit/release:

~~~text
git diff --check
~~~

must pass for first-party changes.

## Worktree hygiene

Release checkpoints should have:

- intended branch;
- intended HEAD;
- exact expected file inventory;
- no unrelated staged files;
- clean worktree after checkpoint commit.

## Version verification

The plugin version must be consistent across release metadata and release
documentation before creating the final tag.

During maintenance development, package metadata may intentionally remain at the
previous public version until the final release bump.

Do not claim an unreleased version is already publicly released.

## Tag integrity

Existing public tags must not be moved.

Historical release tags remain immutable.

A new maintenance release must receive a new tag.

## Release checksum

The final release asset must have its own recorded SHA-256 checksum.

Intermediate development package hashes are verification artifacts and
must not be confused with the checksum of the final public release asset.

## Secret scanning

The release suite scans source/package content for high-signal secret material.

Do not release:

- credentials;
- passwords;
- access tokens;
- private keys;
- production visitor/site data;
- private backups.

## Privacy verification

The release privacy boundary checks that unsupported advanced analytics signals
are not introduced into the FREE core.

The product must remain aggregate-only according to the current contract.

## Documentation verification

Before release, documentation must:

- match current source behavior;
- contain no false release claims;
- document compatibility;
- document configuration;
- document Admin2;
- document public behavior;
- document QR/analytics;
- document privacy/security;
- document storage;
- document upgrade/uninstall;
- document troubleshooting;
- document verification.

## Recommended release order

Use this high-level order:

1. verify exact source checkpoint;
2. run static contract;
3. run PHP syntax checks;
4. run runtime core contract;
5. build deterministic package;
6. verify package content;
7. run clean Grav package install;
8. run QR/tracking/API acceptance;
9. run GPM-style source installation;
10. perform manual browser acceptance;
11. verify documentation;
12. bump final release version when authorized;
13. run final gates again;
14. create immutable release commit/tag;
15. build and checksum final public asset;
16. publish only after final verification.

## Current maintenance evidence

The R2-E checkpoint before documentation work is:

~~~text
8f9bbdf999007f9bd461c461094c755e6438c70e
~~~

Subject:

~~~text
fix: harden analytics event persistence
~~~

At that checkpoint:

~~~text
R2E_FOCUSED_TESTS=PASS
R2E_HTTP_HIGH_VOLUME=PASS
R2E_AUTOMATED_RELEASE_GATES=PASS
R2E_GPM_SOURCE_INSTALL=PASS
~~~

The cumulative development package SHA at that checkpoint was:

~~~text
9aa8d72cce41fa4536fcd05101198d1131e3417db3b5608d1b5d638309ab9cb3
~~~

This hash is not automatically the checksum of a future final 1.0.2 release
asset.

## Final release evidence template

~~~text
BRANCH=
COMMIT=
VERSION=
TAG=
PACKAGE=
SHA256=

STATIC_CONTRACT=
PHP_SYNTAX=
CORE_RUNTIME=
CLEAN_GRAV_INSTALL=
QR_CONTRACT=
TRACKING_CONTRACT=
ADMIN_API_CONTRACT=
GPM_SOURCE_INSTALL=
MANUAL_BROWSER_ACCEPTANCE=
DOCUMENTATION_GATE=
WORKTREE_CLEAN=
~~~

## Related documentation

- [Developer Reference](DEVELOPER_REFERENCE.md)
- [Manual Browser Acceptance Checklist](MANUAL_BROWSER_ACCEPTANCE_CHECKLIST.md)
- [Compatibility](COMPATIBILITY.md)
- [Upgrade](UPGRADE.md)
- [Security](SECURITY.md)
