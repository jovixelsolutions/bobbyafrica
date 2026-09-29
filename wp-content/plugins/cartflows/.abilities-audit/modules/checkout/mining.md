# Checkout Module — Ability Mining

## Module: Checkout (P1)
**Date:** 2026-03-04
**Files Analyzed:**
- `modules/checkout/class-cartflows-checkout.php` (loader)
- `modules/checkout/classes/class-cartflows-checkout-markup.php` (rendering, product config, cart manipulation)
- `modules/checkout/classes/class-cartflows-checkout-fields.php` (field customization)
- `modules/checkout/classes/class-cartflows-checkout-meta-data.php` (settings/meta definitions)
- `modules/checkout/classes/class-cartflows-checkout-ajax.php` (AJAX handlers)
- `modules/checkout/classes/class-cartflows-global-checkout.php` (global checkout override)

---

## Candidate 1: get-checkout-settings

| Field | Value |
|-------|-------|
| **Name** | `get-checkout-settings` |
| **Label** | Get checkout step settings |
| **Description** | Returns the full configuration of a checkout step: products assigned, checkout layout/skin, form field settings (coupon, shipping, additional fields), place order button text, and design options. Use to inspect or audit a checkout step's configuration. |
| **Category** | Configuration |
| **Input** | `{ step_id: integer (required) }` |
| **Output** | `{ step_id, layout, products, form_settings, button_settings, design_settings }` |
| **Permission** | `cartflows_manage_flows_steps` |
| **Meta** | readOnlyHint: true, idempotentHint: true, priority: 1.0, mcp type: resource |

---

## Candidate 2: get-checkout-products

| Field | Value |
|-------|-------|
| **Name** | `get-checkout-products` |
| **Label** | Get checkout products |
| **Description** | Returns the list of WooCommerce products assigned to a checkout step, including product ID, name, quantity, discount type/value, and thumbnail URL. Requires WooCommerce. Use to inspect what products will be added to cart when a customer visits this checkout. |
| **Category** | Configuration |
| **Input** | `{ step_id: integer (required) }` |
| **Output** | `{ step_id, products: [{ product_id, name, quantity, discount_type, discount_value, add_to_cart, img_url, regular_price }] }` |
| **Permission** | `cartflows_manage_flows_steps` |
| **Meta** | readOnlyHint: true, idempotentHint: true, priority: 1.0, mcp type: resource |

---

## Candidate 3: get-checkout-fields

| Field | Value |
|-------|-------|
| **Name** | `get-checkout-fields` |
| **Label** | Get checkout form fields |
| **Description** | Returns the billing and shipping field configuration for a checkout step, including field order, labels, enabled/required/optimized states, widths, and placeholder text. Also returns form-level settings (coupon field, additional fields, ship-to-different toggles). Requires WooCommerce. |
| **Category** | Configuration |
| **Input** | `{ step_id: integer (required), field_type: string (enum: billing, shipping, all; default: all) }` |
| **Output** | `{ step_id, billing_fields: [...], shipping_fields: [...], form_settings: { coupon_field, optimize_coupon, additional_fields, optimize_order_note, ship_to_different } }` |
| **Permission** | `cartflows_manage_flows_steps` |
| **Meta** | readOnlyHint: true, idempotentHint: true, priority: 1.0, mcp type: resource |

---

## Candidate 4: update-checkout-products

| Field | Value |
|-------|-------|
| **Name** | `update-checkout-products` |
| **Label** | Update checkout products |
| **Description** | Sets or replaces the products assigned to a checkout step. Each product entry specifies a WooCommerce product ID, quantity, and optional discount. Requires WooCommerce. Use to configure which products are pre-loaded into the cart when a customer visits the checkout. |
| **Category** | Configuration |
| **Input** | `{ step_id: integer (required), products: array (required) [{ product: integer, quantity: integer, discount_type: string, discount_value: string, add_to_cart: string }] }` |
| **Output** | `{ step_id, products, message }` |
| **Permission** | `cartflows_abilities_api_edit` + `cartflows_manage_flows_steps` |
| **Meta** | readOnlyHint: false, idempotentHint: true, destructiveHint: false, priority: 2.0, mcp type: tool |

---

## Candidate 5: update-checkout-layout

| Field | Value |
|-------|-------|
| **Name** | `update-checkout-layout` |
| **Label** | Update checkout layout |
| **Description** | Changes the checkout skin/layout for a step. Available layouts: modern-checkout, modern-one-column, one-column, two-column. Some layouts (two-step, multistep-checkout) require CartFlows Pro. Use to switch the visual style of a checkout page. |
| **Category** | Configuration |
| **Input** | `{ step_id: integer (required), layout: string (required, enum: modern-checkout, modern-one-column, one-column, two-column) }` |
| **Output** | `{ step_id, layout, message }` |
| **Permission** | `cartflows_abilities_api_edit` + `cartflows_manage_flows_steps` |
| **Meta** | readOnlyHint: false, idempotentHint: true, destructiveHint: false, priority: 2.0, mcp type: tool |

---

## Candidate 6: update-checkout-place-order-button

| Field | Value |
|-------|-------|
| **Name** | `update-checkout-place-order-button` |
| **Label** | Update place order button |
| **Description** | Configures the checkout Place Order button text, lock icon toggle, and price display toggle. Use to customize the CTA on the checkout page. |
| **Category** | Configuration |
| **Input** | `{ step_id: integer (required), button_text: string, show_lock_icon: string (enum: yes, no), show_price: string (enum: yes, no) }` |
| **Output** | `{ step_id, button_text, show_lock_icon, show_price, message }` |
| **Permission** | `cartflows_abilities_api_edit` + `cartflows_manage_flows_steps` |
| **Meta** | readOnlyHint: false, idempotentHint: true, destructiveHint: false, priority: 2.0, mcp type: tool |

---

## Candidate 7: update-checkout-form-settings

| Field | Value |
|-------|-------|
| **Name** | `update-checkout-form-settings` |
| **Label** | Update checkout form settings |
| **Description** | Toggles form-level settings on a checkout step: show/optimize coupon field, show/optimize additional (order notes) field, ship-to-different-address toggle, Google address autocomplete, product images in order review, and cart editing. Only provided fields are updated. |
| **Category** | Configuration |
| **Input** | `{ step_id: integer (required), show_coupon_field, optimize_coupon_field, show_additional_fields, optimize_order_note, ship_to_different_address, google_autoaddress, show_product_images, enable_cart_editing }` (all string: yes/no) |
| **Output** | `{ step_id, settings, message }` |
| **Permission** | `cartflows_abilities_api_edit` + `cartflows_manage_flows_steps` |
| **Meta** | readOnlyHint: false, idempotentHint: true, destructiveHint: false, priority: 2.0, mcp type: tool |

---

## Rejected During Mining (not viable)

| Candidate | Reason |
|-----------|--------|
| Apply coupon to cart (AJAX) | Frontend-only action, requires active WC session/cart context. Not agent-callable. |
| Remove cart product (AJAX) | Frontend-only action, requires active cart session. Not agent-callable. |
| Check email exists (AJAX) | Narrow frontend utility, not useful for agent workflows. |
| Upload checkout file (AJAX) | File upload, requires multipart form. Not suitable for ability API. |
| Override global checkout (runtime) | Purely runtime hook logic, not a discrete action. |
| Configure cart data (runtime) | Tied to page load lifecycle, not callable externally. |
