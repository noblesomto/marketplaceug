# Uganda Localization + Flutterwave Migration — Design

Status: approved (pending final user sign-off on this doc)
Date: 2026-08-02

## Context

MarketplaceUG is currently built as a Nigerian marketplace (Marketplace Naija): Naira currency
hardcoded throughout, states/LGAs seeded with Nigerian data, shipping cost computed via a
Nigeria-only courier API (Agility Logistics), and payments/payouts run through Paystack with
Nigerian bank (NUBAN) transfers. The app is pre-launch — no live user/advert data depends on the
current Nigerian location data, so it can be truncated and reseeded rather than migrated.

This design covers relocalizing the app to Uganda and replacing Paystack with Flutterwave as the
sole payment gateway.

## Goals

1. Locations reflect Uganda: 4 regions, ~146 districts (two-level, no city sub-level).
2. Currency is UGX everywhere (display as "UGX", no minor-unit/kobo-style conversion).
3. Flutterwave fully replaces Paystack for checkout, ad-boost payments, and webhooks.
4. Seller payouts run through Flutterwave Transfers, supporting both Uganda bank transfer and
   mobile money (MTN/Airtel).
5. Shipping cost calculation no longer depends on Agility; replaced with a flat, admin-configurable
   fee per district.
6. Site branding (name, contact info, SEO locale, sitemap) updated to Uganda ("Marketplace Uganda").
7. Phone number validation accepts Uganda mobile numbers instead of Nigerian ones.

Out of scope: multi-currency/multi-country support, live data migration (not needed — pre-launch),
courier API integration (deferred — flat/manual rate for now).

## 1. Locations

**Approach: repoint existing schema, don't rename.** Keep the `states` and `lgas` tables and
`State`/`Lga` models as-is (single-country app, renaming is cosmetic-only churn across ~15 files
for no functional gain). Only the seed data and user-facing labels change:

- `StateSeeder` replaced with Uganda's 4 regions: Central, Eastern, Northern, Western.
- `LgaSeeder` replaced with Uganda's ~146 districts, each `state_id`-linked to its region.
- UI copy changes "State" → "Region" and "LGA" → "District" (blade views, JS labels, admin panel).
- `adverts.state` (string column) and `adverts.state_slug` continue to work unchanged — they'll
  just hold district names/slugs instead of Nigerian LGA names.

**Retire `gig_logistics` and the Agility integration entirely** (not repointed — there's no Uganda
equivalent and the chosen location depth is Region+District, no separate city level):
- Drop `GigLogisticController` usages in `LocationController` (`getGIG`, city dropdowns).
- Remove `getAgilityShippingCost`/`calculateShippingCost` and the `AGILITY_*` config/env block.
- Add a `shipping_fee` column to `lgas` (district table); admin sets a flat fee per district via a
  simple CRUD screen (adapted from the existing `admin/settings/gig/locations.blade.php` pattern).
- Checkout shipping-cost lookup becomes a direct `Lga::find($id)->shipping_fee` read — no external
  API call.

**Phone validation:** replace `NigerianPhoneNumber` rule with `UgandanPhoneNumber` — matches Uganda
mobile formats (`07XXXXXXXX` local / `+2567XXXXXXXX`), applied at the same 4 call sites currently
using `NigerianPhoneNumber`.

**Cleanup:** delete `resources/js/lga.js*` backups tied to Nigerian data assumptions only if their
cascading-dropdown logic needs behavioral changes; otherwise they keep working against relabeled
data untouched. Regenerate `public/sitemap-locations.xml` from the new district seed data.

## 2. Currency

No currency abstraction exists today — `₦` and raw `number_format()` calls are hardcoded in ~40
blade views plus several PHP files, and payment code multiplies/divides by 100 assuming Naira's
kobo minor unit.

- Add `config/currency.php`: `code => 'UGX'`, `symbol => 'UGX'` (per your choice — code, not "USh").
- Add a `money()` helper (or Blade component `<x-money :amount="..." />`) wrapping
  `number_format($amount, 0, '.', ',')` prefixed with the configured symbol; replace all ~40
  hardcoded `₦ {{ number_format(...) }}` call sites with it.
- Remove all `* 100` / `/ 100` conversions in payment controllers (`PaystackController` →
  `FlutterwaveController`, both web and API) — Flutterwave expects whole-shilling amounts for UGX,
  not a minor unit.
- `SyncBanks.php`'s `?currency=NGN` query param becomes `?currency=UGX` (or is replaced — see §3
  payouts, Flutterwave's bank-list endpoint differs from Paystack's).

## 3. Payment gateway: Paystack → Flutterwave (full replacement)

Current integration is a hand-rolled HTTP client (no SDK) against Paystack's REST API, entirely
server-side redirect-based (no client-side JS SDK to replace) — this makes the swap
controller-for-controller with no frontend rework needed beyond copy changes.

**Config/env:** replace `config('services.paystack.*')` block with
`config('services.flutterwave.*')` (`publicKey`, `secretKey`, `encryptionKey`, `paymentUrl`
defaulting to `https://api.flutterwave.com/v3`). Swap `PAYSTACK_*` for `FLW_*` in `.env.example`.

**Checkout (replaces `User/PaystackController.php` + `Api/PaystackController.php`):**
- `POST /v3/payments` (Standard Flutterwave Checkout) replaces `/transaction/initialize`; response
  gives a `link` to redirect to (same shape as Paystack's `authorization_url` — direct swap).
- `GET /v3/transactions/{id}/verify` (by `tx_ref`) replaces `/transaction/verify/{reference}` for
  callback verification.
- Same method surface: `initialize()`, `callback()`, `resumePayment()`, `initialize_boost()`,
  `callback_boost()`, `initialize_post_boost()`, `retry_boost_payment()`, plus API-side mirrors.
- `Payment` model fields stay as-is (`payment_reference`↔`tx_ref`, `trans_id`↔`transaction_id` are
  gateway-agnostic already).

**Webhook (replaces `Api/PaystackWebhookController.php`):**
- Verify Flutterwave's `verif-hash` header (a static secret hash, not HMAC — set via
  `FLW_WEBHOOK_HASH` and compared with `hash_equals`) instead of Paystack's HMAC-SHA512 signature.
- Listen for `charge.completed` event (Flutterwave's equivalent of `charge.success`).
- Keep the existing pattern of re-verifying server-side via the verify endpoint before trusting the
  webhook payload, and keep idempotent boost activation logic.

**Routes:** same route structure/names, just renamed `paystack.*` → `flutterwave.*` and controller
class swapped; `POST /paystack/webhook` → `POST /flutterwave/webhook`.

**Fix while touching this code:** the existing `config('services.paystack.secret')` typo bug in
`ManagePayments.php`/`SyncBanks.php` (should be `secretKey`) gets carried forward correctly as
`config('services.flutterwave.secretKey')` — not reproduced.

## 4. Seller payouts

Current: Paystack Transfers via NUBAN bank recipients only (`ManagePayments::sendPayout`,
`SyncBanks`, `Bank` model). You chose **bank transfer + mobile money** for Uganda.

- `POST /v3/transfers` (Flutterwave) replaces the 3-step Paystack recipient-then-transfer flow —
  Flutterwave Transfers takes destination details directly per-call (no separate "create recipient"
  step), simplifying `sendPayout()`.
- Two payout types: `type: 'account'` with a Uganda `bank_code` (from Flutterwave's Uganda bank
  list) for bank transfer, or `type: 'mobilemoneyuganda'` with the seller's MTN/Airtel number for
  mobile money.
- `UpdatePaymentInfoRequest` gains a payout-method selector (bank vs mobile money) and validates the
  matching fields (`bank_code`/`account_number` vs `mobile_network`/`phone_number`).
- `SyncBanks` command's Paystack bank-list pull becomes Flutterwave's `GET /v3/banks/UG`; `Bank`
  model keeps `name` + gains/renames the code column to a gateway-neutral `bank_code` (was
  `paystack_bank_code`).
- `users` table payout columns (`bank_name`, `bank_code`, `account_name`, `account_number`) gain
  `payout_method`, `mobile_network`, `mobile_money_number`.

## 5. Branding

Update `config/global.php` (`site_name` → "Marketplace Uganda", `site_phone` → Uganda number,
`site_address` → Uganda address), `header-location.blade.php` SEO meta (`Nigeria` → `Uganda`,
`og:locale` `en_NG` → `en_UG`), and regenerate `public/sitemap-locations.xml` from the new district
seed data. Any other Nigeria-specific copy found in blade views during implementation gets swapped
in the same pass.

## Testing

- Location: seed Uganda regions/districts, verify cascading region→district dropdowns and advert
  location filtering work end-to-end.
- Currency: spot-check price display across advert cards, checkout, admin payment screens.
- Payment: sandbox Flutterwave checkout end-to-end (init → redirect → callback → webhook), verify
  boost payment flow, verify webhook signature rejection on tampered payloads.
- Payouts: sandbox transfer to a test Uganda bank account and a test mobile money number.
- Regression: confirm no remaining references to Paystack, Naira (`₦`/`NGN`), Nigerian
  states/LGAs, or Agility in the codebase (`grep -ri` sweep) once implementation is complete.
