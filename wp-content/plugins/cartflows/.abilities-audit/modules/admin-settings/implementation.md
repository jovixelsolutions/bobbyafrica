# Admin/Settings Module — Phase 4 Implementation Notes

**Date:** 2026-03-04
**Phase:** 4 — Implementation (COMPLETE)
**PHP syntax check:** PASS (both files)

---

## Files Modified

### 1. `abilities/class-cartflows-ability-config.php`

Added a new section `// Admin / Settings — Read` and `// Admin / Settings — Write` containing 6 ability entries, inserted just before `); // end $abilities`.

**Entries added:**
- `CARTFLOWS_ABILITY_API_NAMESPACE . 'get-general-settings'`
- `CARTFLOWS_ABILITY_API_NAMESPACE . 'get-store-checkout'`
- `CARTFLOWS_ABILITY_API_NAMESPACE . 'get-permalink-settings'`
- `CARTFLOWS_ABILITY_API_NAMESPACE . 'get-integration-settings'`
- `CARTFLOWS_ABILITY_API_NAMESPACE . 'update-general-settings'`
- `CARTFLOWS_ABILITY_API_NAMESPACE . 'update-permalink-settings'`

### 2. `abilities/class-cartflows-ability-runtime.php`

Added two new sections of execute callbacks immediately before the existing `// Private Helpers` section:
- `// Execute Callbacks — Admin / Settings Read` (4 methods)
- `// Execute Callbacks — Admin / Settings Write` (2 methods)

**Methods added:**
- `get_general_settings( $input )`
- `get_store_checkout( $input )`
- `get_permalink_settings( $input )`
- `get_integration_settings( $input )`
- `update_general_settings( $input )`
- `update_permalink_settings( $input )`

---

## Implementation Details

### `get-general-settings` [Free — read]
- Calls `\Cartflows_Helper::get_common_settings()` which reads `_cartflows_common` with defaults merged.
- Returns: `default_page_builder`, `global_checkout`, `override_global_checkout`, `override_store_order_pay`, `disallow_indexing`.
- Permission: `cartflows_manage_flows_steps`

### `get-store-checkout` [Free — read]
- Calls `\Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' )`.
- Validates that the returned ID is a real `cartflows_flow` post before returning details.
- Returns `is_configured: false` with zeroed fields if no store checkout is set or ID is invalid.
- Permission: `cartflows_manage_flows_steps`

### `get-permalink-settings` [Free — read]
- Calls `\Cartflows_Helper::get_permalink_settings()` which reads `_cartflows_permalink` with defaults merged.
- Returns: `permalink`, `permalink_flow_base`, `permalink_structure`.
- Permission: `cartflows_manage_flows_steps`

### `get-integration-settings` [Free — read]
- Accepts optional `integration` enum: `facebook | google_analytics | google_ads | tiktok | pinterest | snapchat | all` (default: `all`).
- Maps each group to its WP option key and calls `\Cartflows_Helper::get_admin_settings_option()`.
- Returns only the requested group(s) as an object.
- Permission: `cartflows_manage_flows_steps`

### `update-general-settings` [Free — write, option-gated]
- Reads current settings via `\Cartflows_Helper::get_common_settings()`.
- Iterates only `allowed_keys` — ignores any fields not explicitly whitelisted (protects `override_store_order_pay` and other keys from accidental clobber).
- Merges changes via `wp_parse_args( $updates, $current )` — partial updates are safe.
- Saves via `\Cartflows_Helper::update_admin_settings_option( '_cartflows_common', $new_settings, true )` (network-aware).
- Permission: `cartflows_manage_flows_steps` + `cartflows_abilities_api_write` option gate.

### `update-permalink-settings` [Free — write, option-gated]
- Each field is individually optional; throws if no fields provided.
- Falls back to `CARTFLOWS_STEP_PERMALINK_SLUG` / `CARTFLOWS_FLOW_PERMALINK_SLUG` constants if an empty string is passed (mirrors original AJAX handler's reset behaviour).
- Calls `update_option( 'cartflows_permalink_refresh', true )` after save to flush rewrite rules.
- Saves via `\Cartflows_Helper::update_admin_settings_option( '_cartflows_permalink', $new_settings, true )`.
- Permission: `cartflows_manage_flows_steps` + `cartflows_abilities_api_write` option gate.

---

## Schema Decisions

- All read abilities: `idempotentHint: true`, `readOnlyHint: true`, `priority: 1.0`, `type: resource`.
- All write abilities: `idempotentHint: true`, `readOnlyHint: false`, `priority: 2.0`, `type: tool`.
- `get-general-settings`, `get-store-checkout`, `get-permalink-settings`: empty `properties: {}` input schema (no input required).
- `get-integration-settings`: single optional `integration` enum input with default `all`.
- `update-general-settings` / `update-permalink-settings`: all fields optional (no `required` array) — partial updates supported.
- Write gate uses `cartflows_abilities_api_write` option (consistent with existing write abilities in the plugin).
