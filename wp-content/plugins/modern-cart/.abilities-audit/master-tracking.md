# Modern Cart — Abilities Audit: Master Tracking

**Date completed:** 2026-03-04
**Status:** DONE ✅

---

## Summary

| # | Ability ID | File | Type | Score | Status |
|---|-----------|------|------|-------|--------|
| 1 | `moderncart/get-settings` | `inc/abilities/settings/get-settings.php` | Read | Pre-existing | ✅ |
| 2 | `moderncart/update-settings` | `inc/abilities/settings/set-settings.php` | Write (destructive) | Pre-existing | ✅ |
| 3 | `moderncart/get-plugin-status` | `inc/abilities/plugin/get-plugin-status.php` | Read | 16.3/18 | ✅ NEW |
| 4 | `moderncart/get-available-options` | `inc/abilities/settings/get-available-options.php` | Read | 15.3/18 | ✅ NEW |
| 5 | `moderncart/reset-settings` | `inc/abilities/settings/reset-settings.php` | Write (destructive) | 14.7/18 | ✅ NEW |
| 6 | `moderncart/complete-onboarding` | `inc/abilities/plugin/complete-onboarding.php` | Write (idempotent) | 13.3/18 | ✅ NEW |
| 7 | `moderncart/get-cart-summary` | `inc/abilities/cart/get-cart-summary.php` | Read (session) | 12.3/18 | ✅ NEW |

**Total: 7 abilities (2 pre-existing + 5 new)**

---

## Infrastructure Used

- `Abstract_Ability` — base class, all new abilities extend it
- `Register_Abilities::$abilities` — updated to include all 5 new entries
- `Response` — used in all new abilities for consistent success/error returns
- Autoloader — resolves `ModernCart\Inc\Abilities\{Sub}\{Class}` → file path automatically

---

## New Directories Created

```
inc/abilities/
├── plugin/
│   ├── get-plugin-status.php
│   └── complete-onboarding.php
├── cart/
│   └── get-cart-summary.php
└── settings/
    ├── get-available-options.php   (NEW)
    └── reset-settings.php          (NEW)
    (get-settings.php and set-settings.php were pre-existing)
```

---

## Key Implementation Notes

- **`get-plugin-status`**: Uses `Helper::get_pro_status()`, `Helper::is_maintenance_mode()`, and `get_option('moderncart_is_onboarding_complete')`. Falls back to `MODERNCART_VER` constant if version option not yet set.

- **`get-available-options`**: Hardcodes known enum options from the settings schema. Returns `{ value, label }` pairs per field, grouped by option key. Supports `groups[]` filter.

- **`reset-settings`**: `is_destructive=true` → inherits `dry_run` + `_wpnonce` injection from `Abstract_Ability`. Uses `delete_option()` per group; defaults re-apply automatically on next `get_option()` read via `Helper::get_option()`. `resolve_groups()` helper validates against whitelist.

- **`complete-onboarding`**: Overrides `get_annotations()` to return `idempotent=true, destructive=false` (not using `is_destructive` flag since no nonce needed). Uses `moderncart_is_onboarding_complete` option key (matches `plugin-loader.php` and `admin-menu.php`). **Note:** `MCW_ZipWP_Helper::is_onboarding_complete()` checks a different key (`moderncart_onboarding_complete`) — this is a pre-existing inconsistency, not introduced by this audit.

- **`get-cart-summary`**: Overrides `get_annotations()` to set `idempotent=false` (session-specific). Guards against `WC()->cart === null`. Uses `Cart::get_instance()->get_free_shipping_amount()`. Includes `currency` and `currency_symbol` in totals. The `use ModernCart\Inc\Cart` import creates an alias that shadows the current `ModernCart\Inc\Abilities\Cart` namespace for the `Cart` identifier — this is intentional and correct PHP behavior.
