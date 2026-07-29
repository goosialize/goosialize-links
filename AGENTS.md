# AGENTS.md

## Project Identity

This repository contains the standalone Goosialize Links plugin for Grav CMS.

The private GoosBoard project is unrelated and must not be copied, exposed,
renamed, packaged, or treated as the public foundation of this plugin.

## Supported Platform

- Target Grav version: 2.0.12
- Target runtime: PHP 8.x
- Grav 2 official documentation and source behavior are authoritative.
- Compatibility claims require explicit testing.

## Admin2 Rules

All administrator interfaces must use real native Grav Admin2 components and
the Admin2 design system.

Do not introduce:

- custom HTML controls that imitate Admin2;
- Shadow DOM that isolates native Admin2 components;
- an independent admin design system;
- legacy Admin interfaces presented as Admin2;
- unnecessary frontend frameworks inside Admin2.

## Product Architecture

Goosialize Links must:

- work independently from Goosialize Leads;
- keep link-page configuration in its own storage;
- keep analytics in its own storage;
- use an optional adapter for Goosialize Leads;
- avoid provider-specific integrations;
- avoid SendPulse, Mailchimp, Brevo, or CRM logic in the core plugin.

## FREE Analytics Contract

The FREE edition exposes only:

1. total page views;
2. total link clicks;
3. clicks per link or button;
4. QR visits.

The FREE edition must not expose advanced visitor profiling, device reports,
country reports, referrers, UTM reporting, funnels, or complex comparisons.

A QR visit means that the tracked QR redirect URL was opened. It does not claim
to detect the physical scanning action itself.

## FREE Product Limits

- One public Link in Bio page
- Up to eight active links
- Three basic themes
- One QR code
- One optional basic contact form
- Required Goosialize Links branding
- No custom CSS
- No multiple campaigns
- No multiple profiles
- No advanced analytics

## Security Rules

Every implementation must consider:

- permissions and authorization;
- CSRF protection;
- URL validation;
- output escaping;
- safe redirects;
- form validation;
- upload validation;
- analytics abuse resistance;
- privacy-preserving storage;
- safe behavior when optional dependencies are unavailable.

## Development Workflow

For every checkpoint:

1. Verify branch, HEAD, and working-tree state.
2. Declare the exact allowed file inventory.
3. Apply only the approved change.
4. Run syntax, structure, security, and behavior checks.
5. Review the complete diff.
6. Keep changes uncommitted until approval.
7. Commit only after explicit review.
8. Do not rewrite unrelated files.
9. Do not silently expand scope.
10. Stop on manifest or contract contradictions.

Documentation contracts must be updated before implementation when an
architectural decision changes.
