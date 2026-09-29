# Woo Dynamic Flow Module -- Implementation

**Date:** 2026-03-04
**Module:** Woo Dynamic Flow (Module 10)

## Implemented Abilities

### 1. `cartflows/get-product-flow-mapping` (Read)

**Config entry added to:** `class-cartflows-ability-config.php`
**Runtime method added to:** `class-cartflows-ability-runtime.php`

- **Method:** `get_product_flow_mapping( $input )`
- **Permission:** `cartflows_manage_flows_steps`
- **Input:** `product_id` (integer, required)
- **Output:** `product_id`, `product_title`, `flow_id`, `flow_title`, `add_to_cart_text`
- **Validations:**
  - WooCommerce must be active
  - Product must exist (via `wc_get_product`)
  - Flow title resolved only if flow exists with correct post type
- **WooCommerce dependency:** Yes
- **MCP annotations:** `readOnlyHint: true`, `idempotentHint: true`, `type: resource`

### 2. `cartflows/list-product-flow-mappings` (Read)

**Config entry added to:** `class-cartflows-ability-config.php`
**Runtime method added to:** `class-cartflows-ability-runtime.php`

- **Method:** `list_product_flow_mappings( $input )`
- **Permission:** `cartflows_manage_flows_steps`
- **Input:** `paged` (integer, default 1), `per_page` (integer, default 10, max 100)
- **Output:** `mappings` (array), `total`, `total_pages`, `page`
- **Query:** WP_Query on `product` post type with `meta_query` filtering `cartflows_redirect_flow_id` NOT IN ('', '0')
- **WooCommerce dependency:** Yes
- **MCP annotations:** `readOnlyHint: true`, `idempotentHint: true`, `type: resource`

### 3. `cartflows/update-product-flow-mapping` (Write)

**Config entry added to:** `class-cartflows-ability-config.php`
**Runtime method added to:** `class-cartflows-ability-runtime.php`

- **Method:** `update_product_flow_mapping( $input )`
- **Permission:** `cartflows_abilities_api_write` + `cartflows_manage_flows_steps`
- **Input:** `product_id` (integer, required), `flow_id` (integer, optional), `add_to_cart_text` (string, optional)
- **Output:** `product_id`, `flow_id`, `flow_title`, `add_to_cart_text`, `message`
- **Validations:**
  - WooCommerce must be active
  - Product must exist
  - If flow_id > 0, flow must exist with correct post type
  - Button text sanitized via `sanitize_text_field()`
- **WooCommerce dependency:** Yes
- **MCP annotations:** `readOnlyHint: false`, `idempotentHint: true`, `type: tool`

## PHP Syntax Verification

- `php -l class-cartflows-ability-config.php` -- No syntax errors detected
- `php -l class-cartflows-ability-runtime.php` -- No syntax errors detected

## Summary

| Metric | Count |
|--------|-------|
| Abilities implemented | 3 |
| Read abilities | 2 |
| Write abilities | 1 |
