# Manual Browser Acceptance Checklist

This checklist covers human browser verification for Goosialize Links after the
automated release gates have passed.

It is intended for release candidates, maintenance releases, and significant
Admin2/public rendering changes.

## Scope

Manual acceptance verifies browser behavior that automated source/runtime tests
cannot fully prove.

The checklist covers:

- Admin2 plugin editor;
- Live Preview;
- Save behavior;
- public page;
- appearance;
- multilingual content;
- links;
- actions;
- QR;
- analytics;
- permissions;
- responsive behavior;
- browser console health.

## Preconditions

Before starting:

- automated release gates pass;
- package/source under test is known;
- Grav cache is clear;
- browser cache is not masking changes;
- an Admin2 superuser is available;
- a restricted non-superuser is available when permission testing is required.

Record:

~~~text
DATE=
BRANCH=
COMMIT=
PLUGIN_VERSION=
GRAV_VERSION=
ADMIN2_VERSION=
API_VERSION=
PHP_VERSION=
BROWSER=
~~~

## 1. Admin2 plugin page

Open the Goosialize Links plugin editor.

Verify:

- [ ] Plugin page loads without fatal error.
- [ ] Plugin status toggle renders.
- [ ] Live Preview renders.
- [ ] Profile section renders.
- [ ] Appearance section renders.
- [ ] Actions section renders.
- [ ] Links section renders.
- [ ] Native Admin2 Save control remains available.
- [ ] Native form controls remain usable.

Expected result:

~~~text
ADMIN2_PLUGIN_PAGE=PASS
~~~

## 2. Admin2 layout

On desktop, verify:

- [ ] Preview is presented alongside editor content.
- [ ] Preview is on the left.
- [ ] Profile appears in the right-side editor flow.
- [ ] Appearance follows Profile.
- [ ] Actions and Links remain full-width workflow sections.
- [ ] Preview is not sticky.
- [ ] No overlapping panels.
- [ ] No unexpected horizontal overflow.

Expected result:

~~~text
ADMIN2_WORKSPACE_LAYOUT=PASS
~~~

## 3. Responsive Admin2 layout

Reduce viewport width.

Verify:

- [ ] Editor falls back to a usable stacked layout.
- [ ] Preview remains visible.
- [ ] Native controls remain reachable.
- [ ] No horizontal page overflow.
- [ ] Save remains usable.

Expected result:

~~~text
ADMIN2_RESPONSIVE=PASS
~~~

## 4. Live Preview initial load

Verify:

- [ ] Preview iframe loads successfully.
- [ ] Real public template appears.
- [ ] Current saved configuration is represented.
- [ ] Refresh Preview works.
- [ ] Open Public Page works.
- [ ] No fatal preview API error appears.

Expected result:

~~~text
LIVE_PREVIEW_INITIAL=PASS
~~~

## 5. Live Preview realtime appearance

Without saving, change:

- theme;
- accent;
- button shape.

Verify:

- [ ] Theme updates in preview.
- [ ] Accent updates in preview.
- [ ] Button shape updates in preview.
- [ ] Public saved page remains unchanged before Save.
- [ ] Preview changes remain temporary.

Expected result:

~~~text
LIVE_PREVIEW_UNSAVED_APPEARANCE=PASS
~~~

## 6. Live Preview isolation

While editing Goosialize Links, interact with unrelated Admin2 elements.

Verify:

- [ ] No unrelated text is rewritten.
- [ ] No unrelated controls stop working.
- [ ] Admin2 network behavior remains normal.
- [ ] No page-wide mutation side effects appear.
- [ ] No document-wide preview behavior is visible.

Expected result:

~~~text
LIVE_PREVIEW_ISOLATION=PASS
~~~

## 7. Save behavior

Change a supported shared setting and Save.

Verify:

- [ ] Native Save completes.
- [ ] No duplicate save request is visible.
- [ ] Preview returns to Ready state.
- [ ] Saved value persists after reload.
- [ ] Public page reflects the saved value.
- [ ] Temporary preview state does not override the saved value after reload.

Expected result:

~~~text
SAVE_PERSISTENCE=PASS
~~~

## 8. Powered by toggle

Test enabled state.

Verify:

- [ ] Public credit is visible when enabled.

Test disabled state:

~~~yaml
appearance:
  powered_by: false
~~~

Verify:

- [ ] Public credit is hidden after Save.
- [ ] Reload preserves the disabled state.

Expected result:

~~~text
POWERED_BY_TOGGLE=PASS
~~~

## 9. Profile image

Upload a supported image.

Verify:

- [ ] Upload succeeds.
- [ ] Save succeeds.
- [ ] Public image renders.
- [ ] Preview renders the saved image.
- [ ] Reload preserves the image.
- [ ] Image renders across configured languages.

Expected result:

~~~text
PROFILE_IMAGE=PASS
~~~

## 10. Public profile content

Edit Page-owned profile text.

Verify:

- [ ] Profile name renders.
- [ ] Profile title renders.
- [ ] Profile description renders.
- [ ] Changes persist after Page Save.
- [ ] Plugin config Save does not erase Page-owned content.

Expected result:

~~~text
PUBLIC_PROFILE_CONTENT=PASS
~~~

## 11. Links

Create or use a configured link.

Verify:

- [ ] Enabled link renders.
- [ ] Disabled link does not render.
- [ ] Visible title comes from Page content.
- [ ] Correct destination opens.
- [ ] New-tab behavior matches configuration.
- [ ] Link remains functional after reordering.

Expected result:

~~~text
PUBLIC_LINKS=PASS
~~~

## 12. Link tracking

Click a tracked link.

Verify:

- [ ] Response redirects to the saved link destination.
- [ ] Redirect destination is correct.
- [ ] No request-controlled alternate destination is honored.
- [ ] Link-click analytics increment.

Expected result:

~~~text
TRACKED_LINK=PASS
~~~

## 13. Actions

Verify representative action types.

Recommended minimum:

- Website
- Email
- Phone
- WhatsApp

Verify:

- [ ] Website destination works.
- [ ] Email destination uses the expected email scheme.
- [ ] Phone destination uses `tel:`.
- [ ] WhatsApp destination resolves through `https://wa.me/`.
- [ ] Public labels come from Page content.
- [ ] Disabled action does not render.

Expected result:

~~~text
PUBLIC_ACTIONS=PASS
~~~

## 14. Action tracking

Click a tracked web/social action.

Verify:

- [ ] Redirect matches the saved normalized destination.
- [ ] Unknown identity does not resolve.
- [ ] Action-click analytics increment.

Expected result:

~~~text
TRACKED_ACTION=PASS
~~~

## 15. Multilingual profile

On a multilingual site, switch between at least two configured languages.

Verify:

- [ ] Correct profile text appears for language A.
- [ ] Correct profile text appears for language B.
- [ ] Shared profile image remains the same.
- [ ] Shared website URL remains the same.
- [ ] Localized route behavior is correct.

Expected result:

~~~text
MULTILINGUAL_PROFILE=PASS
~~~

## 16. Multilingual links/actions

Verify translated link titles and action labels.

Confirm:

- [ ] Visible title/label changes with language.
- [ ] Destination remains correct.
- [ ] Stable item identity remains unchanged.
- [ ] Aggregate analytics remain associated with the logical item.

Expected result:

~~~text
MULTILINGUAL_ITEMS=PASS
~~~

## 17. Primary QR

Verify primary QR identity:

~~~text
qr_primary
~~~

Check:

- [ ] QR preview appears.
- [ ] PNG can be loaded/downloaded.
- [ ] SVG can be loaded/downloaded.
- [ ] PNG payload is an image.
- [ ] SVG payload is valid SVG.
- [ ] Controlled filenames are used.

Expected result:

~~~text
QR_RENDERING=PASS
~~~

## 18. QR image analytics exclusion

Record current QR visit total.

Load:

- PNG image;
- SVG image.

Verify:

- [ ] QR visit total does not increase from image loading alone.

Expected result:

~~~text
QR_IMAGE_NO_ANALYTICS=PASS
~~~

## 19. Tracked QR

Request the tracked primary QR route.

Verify:

- [ ] One QR visit is recorded.
- [ ] Response is a temporary redirect.
- [ ] Location is the active public Goosialize Links page.
- [ ] No arbitrary external request destination is honored.

Expected result:

~~~text
TRACKED_QR=PASS
~~~

## 20. Analytics dashboard

Verify dashboard data after test interactions.

Confirm:

- [ ] Page views increment.
- [ ] Link clicks increment.
- [ ] Action clicks increment.
- [ ] QR visits increment.
- [ ] Total clicks reflect link + action clicks.
- [ ] Per-item counts align with performed interactions.

Expected result:

~~~text
ANALYTICS_DASHBOARD=PASS
~~~

## 21. Analytics journal

Inspect runtime storage after browser interactions.

Expected directory:

~~~text
user/data/goosialize-links/analytics/
~~~

Verify:

- [ ] Current day `.events` journal exists.
- [ ] Journal permissions are `0640`.
- [ ] New browser events append successfully.
- [ ] No new daily YAML aggregate is created by the hit path.

Expected result:

~~~text
ANALYTICS_JOURNAL=PASS
~~~

## 22. Legacy analytics visibility

When a legacy YAML fixture/data set is available:

- [ ] Legacy `.yaml` totals remain visible.
- [ ] New `.events` totals for the same date are merged.
- [ ] Journal-only dates appear in reporting.

Expected result:

~~~text
ANALYTICS_LEGACY_COMPAT=PASS
~~~

## 23. Analytics fail-open behavior

Use a controlled test environment only.

Make analytics storage temporarily unavailable or otherwise force a known
analytics write failure.

Verify:

- [ ] Public page remains usable.
- [ ] Valid tracked redirect remains usable.
- [ ] Error is logged.
- [ ] No visitor-facing fatal error is caused solely by analytics storage.

Restore storage immediately after the test.

Expected result:

~~~text
ANALYTICS_FAIL_OPEN=PASS
~~~

## 24. Permission: unauthorized account

Use a non-superuser without:

~~~text
api.goosialize-links.qr.read
~~~

Verify:

- [ ] Protected QR/dashboard API is denied.
- [ ] Protected QR download is denied.
- [ ] Public `/bio` remains accessible.

Expected result:

~~~text
PERMISSION_UNAUTHORIZED=PASS
~~~

## 25. Permission: authorized account

Grant the required protected-read permission and any normal Admin2 platform
prerequisite required for the editor workflow.

Verify:

- [ ] QR/dashboard data loads.
- [ ] PNG download works.
- [ ] SVG download works.
- [ ] No QR write controls appear.

Expected result:

~~~text
PERMISSION_AUTHORIZED=PASS
~~~

## 26. Public route safety

Verify default or configured public route.

Check:

- [ ] Route is local to the site.
- [ ] Invalid route configuration is rejected.
- [ ] Existing unrelated Page is not silently overwritten.
- [ ] Page lookup failure does not take down unrelated site routes.

Expected result:

~~~text
PUBLIC_ROUTE_SAFETY=PASS
~~~

## 27. Browser console

Open browser developer tools.

Verify:

- [ ] No uncaught JavaScript exception from Goosialize Links.
- [ ] No repeated failing preview requests.
- [ ] No unrelated Admin2 component errors introduced by the plugin.
- [ ] No console errors during Save caused by Goosialize Links.

Document browser warnings separately from actual errors.

Expected result:

~~~text
BROWSER_CONSOLE=PASS
~~~

## 28. Final public responsive check

Verify the public page at representative widths:

- desktop;
- tablet;
- mobile.

Confirm:

- [ ] No horizontal overflow.
- [ ] Profile remains readable.
- [ ] Actions remain usable.
- [ ] Links remain usable.
- [ ] QR-related public flow remains functional.
- [ ] Powered by visibility is correct.

Expected result:

~~~text
PUBLIC_RESPONSIVE=PASS
~~~

## Final acceptance record

Complete only after all required checks pass.

~~~text
ADMIN2_PLUGIN_PAGE=
ADMIN2_WORKSPACE_LAYOUT=
ADMIN2_RESPONSIVE=
LIVE_PREVIEW_INITIAL=
LIVE_PREVIEW_UNSAVED_APPEARANCE=
LIVE_PREVIEW_ISOLATION=
SAVE_PERSISTENCE=
POWERED_BY_TOGGLE=
PROFILE_IMAGE=
PUBLIC_PROFILE_CONTENT=
PUBLIC_LINKS=
TRACKED_LINK=
PUBLIC_ACTIONS=
TRACKED_ACTION=
MULTILINGUAL_PROFILE=
MULTILINGUAL_ITEMS=
QR_RENDERING=
QR_IMAGE_NO_ANALYTICS=
TRACKED_QR=
ANALYTICS_DASHBOARD=
ANALYTICS_JOURNAL=
ANALYTICS_LEGACY_COMPAT=
ANALYTICS_FAIL_OPEN=
PERMISSION_UNAUTHORIZED=
PERMISSION_AUTHORIZED=
PUBLIC_ROUTE_SAFETY=
BROWSER_CONSOLE=
PUBLIC_RESPONSIVE=
~~~

Final marker:

~~~text
GOOSIALIZE_LINKS_MANUAL_BROWSER_ACCEPTANCE=PASS
~~~

## Related documentation

- [Admin Guide](ADMIN_GUIDE.md)
- [Live Preview](LIVE_PREVIEW.md)
- [Public Page](PUBLIC_PAGE.md)
- [QR Code](QR_CODE.md)
- [Analytics](ANALYTICS.md)
- [Permissions](PERMISSIONS.md)
- [Release Verification](RELEASE_VERIFICATION.md)
