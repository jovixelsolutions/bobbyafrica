# Checkout Module — Implementation Documentation

**Date:** 2026-03-04
**Module:** Checkout (P1)
**Abilities Implemented:** 7
**Files Modified:**
- `cartflows/abilities/class-cartflows-ability-config.php` (7 config entries added)
- `cartflows/abilities/class-cartflows-ability-runtime.php` (7 execute callbacks added)

---

## Implementation Summary

### Read Abilities (3)

#### 1. `cartflows/get-checkout-settings`
- **Config:** Added after `update-permalink-settings` in the Checkout Configuration Read section
- **Runtime:** `get_checkout_settings()` method
- **Meta keys read:** `wcf-checkout-layout`, `wcf-primary-color`, `wcf-base-font-family`, `wcf-checkout-products`, `wcf-show-coupon-field`, `wcf-optimize-coupon-field`, `wcf-checkout-additional-fields`, `wcf-optimize-order-note-field`, `wcf-shipto-diff-addr-fields`, `wcf-google-autoaddress`, `wcf-checkout-place-order-button-text`, `wcf-checkout-place-order-button-lock`, `wcf-checkout-place-order-button-price-display`, `wcf-order-review-show-product-images`, `wcf-remove-product-field`
- **Validation:** Checks step exists, is `CARTFLOWS_STEP_POST_TYPE`, and has `wcf-step-type` = `checkout`
- **WooCommerce check:** `function_exists('WC')`

#### 2. `cartflows/get-checkout-products`
- **Config:** Added in Checkout Configuration Read section
- **Runtime:** `get_checkout_products()` method
- **Meta keys read:** `wcf-checkout-products`
- **Product enrichment:** Uses `wc_get_product()` to resolve name, `get_the_post_thumbnail_url()` for image, `Cartflows_Helper::get_product_original_price()` for regular price
- **Validation:** Same step type validation as above

#### 3. `cartflows/get-checkout-fields`
- **Config:** Added in Checkout Configuration Read section
- **Runtime:** `get_checkout_fields()` method
- **Meta keys read:** `wcf_field_order_billing`, `wcf_field_order_shipping`, plus form toggles
- **Fallback:** If field order meta is empty, falls back to `Cartflows_Helper::get_checkout_fields()` for WooCommerce defaults
- **Input:** Optional `field_type` enum (billing, shipping, all)

### Write Abilities (4)

#### 4. `cartflows/update-checkout-products`
- **Config:** Added in Checkout Configuration Write section
- **Runtime:** `update_checkout_products()` method
- **Meta key written:** `wcf-checkout-products`
- **Validation:** Each product ID validated via `wc_get_product()`. Generates `unique_id` for each entry.
- **Write gate:** `get_option('cartflows_abilities_api_write', false)`

#### 5. `cartflows/update-checkout-layout`
- **Config:** Added in Checkout Configuration Write section
- **Runtime:** `update_checkout_layout()` method
- **Meta key written:** `wcf-checkout-layout`
- **Enum validation:** Input schema restricts to Free layouts only: `modern-checkout`, `modern-one-column`, `one-column`, `two-column`. Pro layouts (`two-step`, `multistep-checkout`) are excluded.
- **Write gate:** `get_option('cartflows_abilities_api_write', false)`

#### 6. `cartflows/update-checkout-place-order-button`
- **Config:** Added in Checkout Configuration Write section
- **Runtime:** `update_checkout_place_order_button()` method
- **Meta keys written:** `wcf-checkout-place-order-button-text`, `wcf-checkout-place-order-button-lock`, `wcf-checkout-place-order-button-price-display`
- **Partial update:** Only provided fields are updated; omitted fields retain current values
- **Write gate:** `get_option('cartflows_abilities_api_write', false)`

#### 7. `cartflows/update-checkout-form-settings`
- **Config:** Added in Checkout Configuration Write section
- **Runtime:** `update_checkout_form_settings()` method
- **Meta keys written (map):**
  - `show_coupon_field` -> `wcf-show-coupon-field`
  - `optimize_coupon_field` -> `wcf-optimize-coupon-field`
  - `show_additional_fields` -> `wcf-checkout-additional-fields`
  - `optimize_order_note` -> `wcf-optimize-order-note-field`
  - `ship_to_different_address` -> `wcf-shipto-diff-addr-fields`
  - `google_autoaddress` -> `wcf-google-autoaddress`
  - `show_product_images` -> `wcf-order-review-show-product-images`
  - `enable_cart_editing` -> `wcf-remove-product-field`
- **Partial update:** Only provided fields are updated
- **Write gate:** `get_option('cartflows_abilities_api_write', false)`

---

## Patterns Followed

- All abilities use `$this->init($input, CARTFLOWS_ABILITY_API_NAMESPACE . 'slug')` for input parsing
- All abilities use `$this->input_get('field')` to read parsed values
- All abilities use `$this->error($e)` for error responses
- All read abilities have `idempotentHint: true`, `readOnlyHint: true`
- All write abilities are gated behind `get_option('cartflows_abilities_api_write', false)` in permission_callback
- All abilities check `function_exists('WC')` since checkout requires WooCommerce
- All abilities validate step exists and is checkout type before proceeding
- Input schemas use `'type' => 'integer'` for IDs, `'required' => [...]` at object level
- All output values escaped with `esc_html()`, `esc_url()` as appropriate

## PHP Syntax Validation

Both files pass `php -l` syntax check with no errors.
