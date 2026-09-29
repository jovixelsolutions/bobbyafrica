# Modern Cart — Abilities Audit: Discovery

**Date:** 2026-03-04
**Plugin:** Modern Cart Starter for WooCommerce
**Version:** 1.0.7
**Slug:** modern-cart
**Namespace:** ModernCart
**Constant Prefix:** MODERNCART_

---

## Existing Abilities (Already Implemented)

| ID | File | Class | Type |
|----|------|-------|------|
| `moderncart/get-settings` | `inc/abilities/settings/get-settings.php` | `Get_Settings` | Read |
| `moderncart/update-settings` | `inc/abilities/settings/set-settings.php` | `Set_Settings` | Write (destructive) |

Infrastructure in place:
- `Abstract_Ability` — base class with nonce injection, dry_run, capability re-check
- `Register_Abilities` — loader with `moderncart_abilities` filter (Pro plugin extension point)
- `Response` — consistent response helper
- `MCW_ZipWP_Helper` — MCP-stable settings API layer

---

## Plugin Architecture

### AJAX Endpoints (frontend/session, not suitable for Abilities API)
- `moderncart_add_to_cart` — add product to WooCommerce session cart
- `moderncart_remove_product` — remove cart item by key
- `moderncart_update_cart` — update cart item quantity
- `moderncart_apply_coupon` — apply coupon to session cart
- `moderncart_remove_coupon` — remove coupon from session cart
- `moderncart_refresh_slide_out_cart` — refresh cart HTML (UI-only)
- `moderncart_refresh_floating_cart` — refresh floating cart HTML (UI-only)

### Admin AJAX
- `moderncart_update_settings` — settings save (covered by `update-settings` ability)
- `moderncart_fetch_whats_new` — changelog feed
- `moderncart_complete_onboarding` — mark onboarding complete

### Settings Options (4 groups)
- `moderncart_setting` — enable/disable toggles, AJAX, shipping bar, express checkout
- `moderncart_cart` — cart style, theme, image size, coupon field, padding, labels
- `moderncart_floating` — position, icon selection, colors
- `moderncart_appearance` — colors, header alignment, font size

---

## Module Map

### Module 1: Settings & Configuration (DONE)
**Priority:** P0
**Files:** `inc/abilities/settings/*.php`, `inc/integrations/mcw-zipwp-helper.php`
**Status:** 2/2 abilities already implemented

### Module 2: Plugin Status & Diagnostics (NEW)
**Priority:** P0
**Files:** `inc/helper.php`, `plugin-loader.php`, `inc/integrations/mcw-zipwp-helper.php`
**Estimated abilities:** 3
**Key sources:**
- `Helper::get_pro_status()` — returns 'not-installed' | 'active' | 'inactive'
- `MCW_ZipWP_Helper::is_onboarding_complete()` — boolean
- `Helper::is_maintenance_mode()` — Elementor/WooCommerce/WP core check
- `get_option('moderncart_version')` — current/previous version

### Module 3: Cart State (NEW)
**Priority:** P1
**Files:** `inc/cart.php`, `inc/helper.php`
**Estimated abilities:** 1
**Key sources:**
- `Helper::is_cart_empty()`, `Helper::get_cart_count()`
- `Cart::get_free_shipping_amount()`, `Cart::render_free_shipping_bar()`
- WC cart totals: subtotal, discount, tax, shipping, total

### Module 4: Settings Extended (NEW)
**Priority:** P1
**Files:** `inc/helper.php`, `admin-core/inc/settings-fields.php`
**Estimated abilities:** 2
**Key sources:**
- `Helper::get_defaults(true)` — full schema with types
- Reset settings to defaults
- Get available enum options for settings

---

## Total Gap Analysis
- **Implemented:** 2 abilities
- **New candidates:** 5 abilities
- **Expected final count:** 5–7 abilities (pending user selection)
