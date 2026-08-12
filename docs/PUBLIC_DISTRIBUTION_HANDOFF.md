# Public Distribution Handoff

This document defines the inputs for the future GitHub and goosialize.com FREE
release-candidate distribution. It does not authorize publication.

## Release identity

- Product: Goosialize Links
- Edition badge: FREE
- Version/tag: `1.0.0-rc.1`
- Release title: `Goosialize Links 1.0.0-rc.1`
- Description: Self-hosted multilingual Link-in-Bio plugin for Grav CMS with a
  native EN/EL Page workflow, physical Page route ownership, authenticated
  realtime preview for shared settings, tracked links, aggregate analytics,
  profile-image fallback, and one tracked QR code.
- License: MIT
- Compatibility: Grav 2.0.12; PHP 8.3+

## Canonical public URLs

- Source: <https://github.com/goosialize/goosialize-links>
- Releases: <https://github.com/goosialize/goosialize-links/releases>
- Documentation: <https://github.com/goosialize/goosialize-links#readme>
- Release notes: `docs/RELEASE_NOTES_1.0.0-rc.1.md`
- Product page: **PENDING** — no goosialize.com slug is approved

## Download contract

- Asset: `goosialize-links-1.0.0-rc.1.zip`
- Checksum artifact: `goosialize-links-1.0.0-rc.1.zip.sha256`, generated after
  the packaged documentation is frozen
- GitHub download URL: available only after repository, tag, and release exist
- goosialize.com download URL: pending approved product/download architecture

## Publication checks

- [x] Configure the canonical GitHub remote
- [x] Merge the approved release candidate to local `main`
- [ ] Push the reviewed local `main` only after explicit approval
- [ ] Create and push tag only after explicit approval
- [ ] Create the GitHub release from the repository-owned release notes
- [ ] Attach the exact deterministic asset
- [ ] Download the public asset and independently verify SHA-256
- [ ] Approve the goosialize.com product-page slug
- [ ] Add FREE badge, compatibility, license, source, documentation, changelog,
      download, and checksum information to the product page
- [ ] Verify the goosialize.com download resolves to the approved artifact
- [ ] Submit to Grav GPM only after the public repository and release exist
