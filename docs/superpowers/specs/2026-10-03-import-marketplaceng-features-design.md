# Import marketplaceng features into marketplaceug

- Date: 2026-10-03
- Status: approved for planning

## Context

`marketplaceug` (this project) and `marketplaceng` (`/home/www/laravel/marketplaceng`) share 299
commits up to `8eece14` ("Clean up unnecessary files...", 2026-08-02). After that point they
diverged independently:

- **marketplaceug**: 41 commits of Uganda localization — UGX currency via a `money()` helper,
  Uganda regions/districts replacing Nigerian states, Flutterwave replacing Paystack, domain/email
  changes from marketplace.ng to marketplace.ug, Postman collection and phone-sample localization.
- **marketplaceng**: 200 commits of feature work, bug fixes, UI redesigns, and internal refactors.

Despite marketplaceng's "Phase 1–7 restructuring" commits, `app/` directory structure is
**identical** between the two projects (verified via `find app -type d` diff) — only file content
diverged, and marketplaceng has 22 more PHP files (new feature files). This makes diff-based
porting feasible without a structural reconciliation step.

## Goal

Bring the user-facing features and fixes marketplaceng has accumulated since the fork into
marketplaceug, without reintroducing any Nigeria-specific content (currency, regions, domain,
payment gateway, phone formats) that marketplaceug has already localized away.

## Non-goals

- Porting marketplaceng's internal-only refactor commits (controller/service extraction, the
  "Phase 1–4 optimization" and "Phase 1–7 restructuring" series, `HasUserSession` trait rollout).
  These are marketplaceng's own code-quality cleanup, not features, and porting them risks
  destabilizing files without user-visible benefit.
- Replaying marketplaceng's git history via cherry-pick. 200 commits vs. 41 independent
  localization commits touching overlapping files would produce high conflict volume that is easy
  to resolve incorrectly (e.g., silently reverting a localization change).
- A byte-for-byte port of marketplaceng's Payments admin tools (Phase 10) — that group is written
  against Paystack; marketplaceug runs Flutterwave. The concept is ported, not the code.

## Approach

For each phase below, compute the net diff in marketplaceng between the fork point and HEAD for
the files that phase's feature touches:

```
cd /home/www/laravel/marketplaceng && git diff 8eece14 HEAD -- <file-or-path>
```

Then hand-apply the relevant hunks onto marketplaceug's current version of the same file,
adapting any Nigeria-specific identifiers to marketplaceug's existing Uganda/Flutterwave
equivalents as part of the same edit (not as a follow-up pass). Each phase lands as its own
commit(s), so a regression in one phase can be bisected or reverted without losing other phases.

### Cross-cutting localization-safety checklist (apply on every phase)

Before treating a ported change as done, grep the diff being applied for:

- `NGN`, `₦`, `Naira`, `Nigeria`, `marketplace.ng`
- Nigerian state/city names (Lagos, Abuja, Kano, Port Harcourt, etc.)
- Paystack-specific calls/package usage (`paystack`, `Paystack`)
- Nigerian phone formats (`+234`, `080`, `081`, etc.)

And reconcile against what marketplaceug already established:

- UGX via the `money()` helper (added in `55f6dbd`)
- Uganda regions/districts (replacing Nigerian states, `0791e58`)
- Flutterwave (replacing Paystack, `85688e2` and related)
- `+256` phone format
- marketplace.ug domain/emails

Files marketplaceug has already modified for localization get extra scrutiny before any phase
touches them again: `config/app.php`, `config/cors.php`, `routes/web.php`, `.env.example`,
`serve.sh`, `public/frontend/images/logo*.png`, `resources/views/user/settings/get-verified.blade.php`.

## Phases

Ordered by dependency and risk — foundational/security first, cosmetic last. Each phase is a
candidate for its own implementation-plan task; some may be split further during planning if a
phase turns out to be larger than expected.

### Phase 1 — Security/auth hardening

Idempotency-key guard on `createAdvert` (web + API), phone-required enforcement on social/Apple
login and the mobile API, email-verification-loop fix, API login-enumeration fix, registration
source tracking, disabled-user blocking on advert/dashboard/block-user routes, CORS/rate-limiting/
OTP hardening, Admin 2FA (TOTP), CSP headers.

Key ng commits: `f8f13ec`, `228845b`, `1f31fff`, `e79ab3f`, `68efcaa`, `6416b10`, `88ccb8e`,
`6adfd4d`.

Risk: touches `config/cors.php` and `routes/web.php`, both already modified for localization —
diff carefully against current uncommitted state before applying.

### Phase 2 — Ad moderation & integrity

ALL-CAPS title/description normalization (create/update API + admin edit), ban-with-reason +
resubmit/review workflow, configurable image requirements (admin-settable, exposed via API),
reject submissions under the minimum image count, ad-report fixes (N/A display bug, preserve
seller identity after ad deletion, repair admin report management), Fixed-Price-ads-at-price-0 fix.

Key ng commits: `f8f13ec`, `95c601a`, `59f419a`, `8c6ca1f`, `5cb12df`, `511e382`, `97f47c1`,
`218ad73`.

### Phase 3 — Activity log

Site-wide activity log + wiring the dead "View Activity" admin button.

Key ng commit: `9f6923a`.

### Phase 4 — Admin RBAC & dashboard

`manage_settings` permission, role-based dashboard statistics, admin forgot/reset password,
redesigned KPI dashboard (horizontal cards, responsive).

Key ng commits: `c3a4985`, `c3d99ac`, `8de14b2`, `5fdd14d`.

Depends on Phase 3 (dashboard links to the activity log).

### Phase 5 — Search & filtering

FilterService centralization, car/phone filter endpoints, fixed vehicle/phone filter counts,
unified `POST /api/search/filter`, search-by-5-digit-ad-ID.

Key ng commits: `dd2f7fc`, `433575b`, `f4cb7e2`, `929844d`, `db20d43`, `12593c1`, `c44f4f2`.

### Phase 6 — Messaging/chat redesign

Two-panel desktop chat layout, paperclip attachment icon, offer-message formatting (comma
amounts, revised copy), duplicate-conversations fix, unread-count-vs-archived fix, archived-
messages page redesign, chat-form AJAX CSP/jQuery-CDN fix.

Key ng commits: `4748129`, `f1e4bb7`, `c7e14b1`, `36791b1`, `fc3ac32`, `4b3b0a0`, `532e5a3`,
`274fbae`.

### Phase 7 — Notifications

Mark-notification-as-read endpoint + unread-count API, "GET no longer auto-marks all read" fix,
notification ENUM-truncation fix, iOS push fixes (explicit `aps.alert`, dynamic badge count),
monthly stale-device-token cleanup command.

Key ng commits: `f297e62`, `627fa6f`, `17e96ae`, `8ad8d54`, `821e172`.

### Phase 8 — Orders & shipping

Buyer Order Details page + API, buyer shipping-status notifications (in-app + push, including
shipping-company name), Resume Pending Payment API, 8-char alphanumeric `order_code` generation,
pending/abandoned payments surfaced in My Orders with a Complete Payment CTA.

Key ng commits: `3080efb`, `7e94278`, `82b24c5`, `69334df`, `2d8e700`, `801dade`, `aa5b5a0`.

### Phase 9 — Seller pages

Seller followers/following pages, shipper portal redesign, unfollow-button label/capitalization
fixes, Remove Follower API endpoint, follow-endpoint auth/Postman fixes, compact follower list UI.

Key ng commits: `e5e7f0d`, `7675b73`, `e802c48`, `91f47ee`, `8998869`, `be503bc`, `924ccd2`.

Loosely depends on Phase 8 for shipper-facing order data.

### Phase 10 — Payments admin tools (Flutterwave re-implementation, not a diff port)

Unmatched-payments reconciliation: persist payments that don't match a boost/payment record
instead of relying on cache-only dedup, an admin view/controller/routes to complete them, and
admin alerting when one occurs. Boost-payment flow idempotency + stuck-record recovery.

Key ng commits (as reference for *behavior*, not code): `9c0adc8`, `4e011b0`, `11f0cdc`, `77485ab`,
`02411af`, `22b15de`, `b4cba60`, `399fe11`, `27a41b9`.

Reimplement against marketplaceug's existing Flutterwave integration and webhook shape — do not
port Paystack API calls or webhook payload assumptions.

### Phase 11 — SEO/taxonomy

Slug-collision disambiguation (parent-qualified suffix) for Brands/SubCategory/VehicleModel,
`slug_redirects` table + resolver, 301 redirects for renamed category/subcategory/brand/location
URLs, backfill command with test coverage, sitemap gating for thin location+facet URLs, exclude
catch-all Other/No-Name pages from the sitemap.

Key ng commits: `30976ef`, `96c9e58`, `fc5a558`, `ac044f4`, `38e00fd`, `d72b398`, `bb3d70b`,
`ba0729b`, `53da031`, `3b21a9d`.

No localization risk — operates on taxonomy slug structure, not region/currency data.

### Phase 12 — Mobile app polish + merchant feed

Mobile app install banner with device-aware store redirect (sticky on mobile), Google Merchant
feed fixes (buy_direct custom label, exclude animals/non-products/unknown categories),
`display_image_url` (800x600) added to the Advert API response.

Key ng commits: `373219b`, `6609b51`, `87dd7f5`, `1ed4d7d`, `0bae851`.

### Phase 13 — Misc UI fixes

CSP/jQuery-CDN sweep across all pages (beyond the chat-specific fix in Phase 6), blurred-backdrop
image component (stretched/blurred thumbnail fix, scoped to the advert-detail slider, flat-grey
fallback), admin-manageable sister-sites flag + mobile API.

Key ng commits: `1361e96`, `50088d8`, `ede01e7`, `fc738d8`, `5a7df04`.

Explicitly excludes the NG-specific "Ethiopia flag icon" asset tweak (`9f32a53`) — not relevant to
marketplaceug.

## Testing

Each phase gets its own test coverage following the project's existing conventions (feature/unit
tests under the relevant `tests/` subdirectory). Where marketplaceng added test coverage for a
ported feature (e.g. `ba0729b` for the taxonomy backfill command), port the tests too and adapt
fixtures to Uganda data. After each phase: run the full test suite, run the localization-safety
grep checklist above against the phase's diff, and manually smoke-test the feature before
committing and moving to the next phase.

## Error handling

Ported code should preserve marketplaceng's error-handling behavior (its fixes exist because
real production bugs were found), but any error message, log string, or notification copy that
references Nigeria-specific terms must be localized to Uganda during the port, not left for a
follow-up pass.

## Rollout

One commit (or small commit series) per phase, in the order above, each reviewed and tested before
starting the next. No feature flags — this is a direct code import, not a gradual rollout.
