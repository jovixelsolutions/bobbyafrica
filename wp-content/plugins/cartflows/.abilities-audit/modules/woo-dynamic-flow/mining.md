# Woo Dynamic Flow Module -- Ability Mining

**Module:** Woo Dynamic Flow (Module 10)
**Priority:** P2
**Directory:** `cartflows/modules/woo-dynamic-flow/`
**WC Required:** Yes
**Date:** 2026-03-04

---

## Source Files Reviewed

- `class-cartflows-woo-dynamic-flow.php` -- Entry point / loader
- `classes/class-cartflows-wd-flow-loader.php` -- File includes
- `classes/class-cartflows-wd-flow-product-meta.php` -- Product meta tab, save, AJAX search
- `classes/class-cartflows-wd-flow-product-actions.php` -- Redirect on add-to-cart, button text override
- `classes/class-cartflows-wd-flow-actions.php` -- Cart configuration skip, hidden fields, step redirect
- `classes/class-cartflows-wd-flow-shortcodes.php` -- `[cartflows_product_title]`, `[cartflows_product_add_to_cart]`

---

## Product Meta Keys Identified

| Key | Type | Description |
|-----|------|-------------|
| `cartflows_redirect_flow_id` | int | Flow ID to redirect to after add-to-cart |
| `cartflows_add_to_cart_text` | string | Custom add-to-cart button text override |

---

## Ability Candidates

### 1. get-product-flow-mapping (Configuration/Read)
Returns the flow mapping for a single WooCommerce product: flow ID, flow title, and custom button text.

### 2. list-product-flow-mappings (Configuration/Read)
Returns a paginated list of all WooCommerce products that have a CartFlows flow mapping configured.

### 3. update-product-flow-mapping (Configuration/Write)
Sets or clears the flow mapping and button text on a WooCommerce product. Set flow_id to 0 to clear.

---

## Rejected During Mining

- **Shortcodes** (`cartflows_product_title`, `cartflows_product_add_to_cart`): Frontend rendering only, no data access value for an agent.
- **AJAX flow search** (`wcf_json_search_flows`): Already covered by `cartflows/list-flows` which provides the same flow lookup by title.
- **Cart configuration skip / hidden fields**: Internal runtime behavior, not configurable via API.
- **Step redirect logic**: Runtime behavior tied to add-to-cart flow, not a configurable setting.

---

## Notes

The Woo Dynamic Flow module connects WooCommerce products to CartFlows funnels via product meta. The 3 abilities cover the full CRUD surface: inspect a single mapping, list all mappings, and set/clear mappings. All require WooCommerce active.
