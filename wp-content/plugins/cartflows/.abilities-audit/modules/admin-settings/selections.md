# Admin/Settings Module — Phase 3 User Selections

**Date:** 2026-03-04
**Module:** Admin/Settings
**Phase:** 3 — User Selection (COMPLETE)

---

## Selected Abilities (All 6 Approved)

| # | Ability Name | Score | Decision |
|---|-------------|-------|----------|
| 1 | `cartflows/get-general-settings` | 18/18 | SELECTED |
| 2 | `cartflows/get-store-checkout` | 18/18 | SELECTED |
| 3 | `cartflows/get-permalink-settings` | 17/18 | SELECTED |
| 4 | `cartflows/get-integration-settings` | 16.3/18 | SELECTED |
| 5 | `cartflows/update-general-settings` | 16.3/18 | SELECTED |
| 6 | `cartflows/update-permalink-settings` | 15.7/18 | SELECTED |

---

## Batch Summary

**Batch 1** (Reads — unanimous selections):
- `cartflows/get-general-settings` — 18/18
- `cartflows/get-store-checkout` — 18/18
- `cartflows/get-permalink-settings` — 17/18

**Batch 2** (Reads + Writes):
- `cartflows/get-integration-settings` — 16.3/18
- `cartflows/update-general-settings` — 16.3/18
- `cartflows/update-permalink-settings` — 15.7/18

---

## Implementation Notes

- Read abilities use `cartflows_manage_flows_steps` capability (consistent with existing abilities).
- Write abilities (`update-general-settings`, `update-permalink-settings`) require both the capability AND the `cartflows_abilities_api_write` option gate.
- `update-general-settings` merges new values into existing `_cartflows_common` array via `wp_parse_args` to avoid wiping unrelated keys.
- `update-permalink-settings` must call `update_option( 'cartflows_permalink_refresh', true )` after saving to flush rewrite rules.
- `get-store-checkout` reads `_cartflows_store_checkout` via `\Cartflows_Helper::get_global_setting()`.
- `get-integration-settings` supports an optional `integration` filter (enum) defaulting to `all`.
