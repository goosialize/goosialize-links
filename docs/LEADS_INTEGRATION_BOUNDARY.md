# Goosialize Leads Integration Boundary

## Status

Draft boundary only.

The final implementation contract must be confirmed after the standalone
Goosialize Leads public integration surface is stable.

## Dependency Direction

Allowed:

`Goosialize Links -> public Goosialize Leads integration contract`

Not allowed:

`Goosialize Links -> internal Leads repositories or private classes`

Goosialize Leads must not depend on Goosialize Links.

## Core Independence

The following must operate without Goosialize Leads:

- public link page;
- profile and links;
- design settings;
- QR generation;
- QR redirect tracking;
- page-view tracking;
- link-click tracking;
- analytics dashboard.

## Capability Detection

The integration must use explicit capability detection.

It must not assume compatibility merely because a plugin folder, class, or
service name exists.

## Proposed Submission Data

A future normalized submission may include:

- source identifier;
- form identifier;
- name;
- email;
- optional telephone;
- message;
- consent state;
- page route;
- link or block identifier;
- QR-origin state;
- language;
- existing campaign metadata.

The final field names and response contract remain deferred.

## Failure Behavior

If Leads is unavailable:

- links and analytics remain operational;
- the Admin displays a clear integration status;
- the public page does not expose a broken form;
- no submission is silently lost;
- no provider-specific fallback is attempted.

## Forbidden Coupling

Goosialize Links must not directly access:

- Leads storage files;
- Leads internal repositories;
- Leads Admin controllers;
- Leads private API routes;
- provider-addon classes;
- SendPulse credentials;
- CRM credentials.
