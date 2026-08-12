# Goosialize Links FREE v1 Release Checklist

Release candidate: `1.0.0-rc.1`

- [x] Release-candidate branch and starting state verified clean
- [x] Version synchronized across metadata, documentation, and release gates
- [x] CHANGELOG contains consolidated FREE v1 RC notes
- [x] Installation, configuration, permissions, privacy, and retention documented
- [x] MIT License included for the FREE core
- [x] FREE/PAID boundary documented and tested
- [x] Automated release gates pass on Grav 2.0.12
- [x] P9 human-browser acceptance passed
- [x] UX1 responsive Admin2 preview/editor browser acceptance passed
- [x] English and Greek public-content and routing acceptance passed
- [x] UX2 native Page architecture and physical route ownership verified
- [x] Native EN/EL Page translation workflow verified
- [x] Non-destructive and idempotent provisioning verified
- [x] Authenticated realtime preview and analytics suppression verified
- [x] Shared profile-image resolution and fallback correction verified
- [x] RC3 automated and human-browser verification passed
- [x] Stable cross-language Link/Action identity and analytics verified
- [x] Deterministic package reproducibility verified
- [x] Clean package installation and runtime behavior verified
- [x] Authorized and unauthorized permission paths verified
- [x] Privacy and secret scans pass
- [x] Deterministic RC package reproduced independently
- [x] Release-candidate changes committed

## Public distribution pending

- [x] Canonical GitHub remote configured
- [x] Release candidate merged to local `main` by explicit approval
- [ ] Tag `1.0.0-rc.1` created
- [ ] Local `main` and tag pushed
- [ ] GitHub release `Goosialize Links 1.0.0-rc.1` created
- [ ] Asset `goosialize-links-1.0.0-rc.1.zip` attached
- [ ] Public asset SHA-256 independently verified
- [ ] Grav GPM submission completed
- [ ] goosialize.com product-page URL approved and page prepared
- [ ] goosialize.com download and checksum verified
- [ ] Final `1.0.0` promotion reviewed after RC acceptance

The final RC checksum is generated after the packaged documentation is frozen
and published as the external sidecar
`goosialize-links-1.0.0-rc.1.zip.sha256`. Keeping the checksum outside the ZIP
avoids a self-referential archive contract.

Push, tag, GitHub release, GPM, website, and publication steps remain pending
until separately authorized.
