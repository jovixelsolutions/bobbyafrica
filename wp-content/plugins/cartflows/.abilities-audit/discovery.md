# CartFlows Free — Abilities Audit Discovery

**Date:** 2026-03-04
**Plugin:** CartFlows Free v2.2.2
**Plugin slug:** `cartflows`
**Audit phase:** Phase 0 complete

---

## Plugin Architecture Summary

CartFlows Free is a WooCommerce-dependent funnel/checkout builder built on two primary entity types:

- **Flow** (`cartflows_flow` CPT): The funnel container — holds ordered Steps.
- **Step** (`cartflows_step` CPT): Individual pages within a funnel (checkout, thank-you, landing, optin).

### Core Admin Layers

| Layer | Directory | Purpose |
|-------|-----------|---------|
| Ajax handlers | `admin-core/ajax/` | CRUD, lifecycle, stats operations |
| REST API | `admin-core/api/` | Read endpoints for flow and step data |
| Helper | `classes/class-cartflows-helper.php` | Settings access, global options |
| Modules | `modules/{type}/` | Per-step-type rendering logic |

### Custom Capabilities Used

- `cartflows_manage_flows_steps` — Manage funnels and steps (flows/steps CRUD)
- `cartflows_manage_settings` — Manage global plugin settings

### Key Settings Store

- `_cartflows_common` option — serialized array of global settings (page builder, global checkout, tracking)
- `_cartflows_store_checkout` option — ID of the store checkout flow
- Per-flow meta: `wcf-steps`, `wcf-testing`, `wcf-flow-id`, `wcf-step-type`

---

## Modules Inventory

| Module | Directory | Priority | Rationale |
|--------|-----------|----------|-----------|
| Flow (Funnel) | `admin-core/ajax/flows.php`, `admin-core/api/flows.php`, `admin-core/api/flow-data.php` | P0 | Core funnel CRUD — list, get, create, update, delete, clone, status |
| Step | `admin-core/ajax/steps.php`, `admin-core/api/step-data.php` | P0 | Core step CRUD — get, update, clone, delete |
| Analytics/Stats | `admin-core/ajax/flows-stats.php` | P0 | Revenue + order stats by date range and flow |
| Admin/Settings | `admin-core/ajax/common-settings.php`, `classes/class-cartflows-helper.php` | P1 | Global settings read/write |
| Checkout module | `modules/checkout/` | P1 | Checkout step settings |
| Thank You module | `modules/thankyou/` | P1 | Thank-you step settings |
| Optin module | `modules/optin/` | P1 | Opt-in step settings |
| Landing module | `modules/landing/` | P1 | Landing page step settings |
| Email Report | `modules/email-report/` | P2 | Email report config |
| Woo Dynamic Flow | `modules/woo-dynamic-flow/` | P2 | Dynamic product routing |
| Gutenberg | `modules/gutenberg/` | P2 | Block registration |
| Elementor | `modules/elementor/` | P2 | Elementor widget |

---

## Key Operations Identified (pre-mining summary)

### Flow operations (admin-core/ajax/flows.php)
- `get_items` (REST) — list flows with pagination, status, search
- `get_item` (REST) — get full flow data including steps
- `update_flow_title` — rename a flow
- `clone_flow` — duplicate a flow with all steps
- `delete_flow` — permanent delete
- `trash_flow` — move to trash
- `restore_flow` — untrash
- `reorder_flow_steps` — change step order in a flow
- `trash_flows_in_bulk` — bulk trash
- `update_flow_post_status` — bulk status change
- `delete_flows_permanently` — bulk permanent delete
- `save_flow_meta_settings` — save flow settings (title, slug, meta)
- `export_flows_in_bulk` — export flow data as JSON
- `update_status` — toggle single flow publish/draft
- `get_published_flows` — list published flow IDs+titles for analytics

### Step operations (admin-core/ajax/steps.php)
- `get_item` (REST) — get step data with type, settings, options
- `clone_step` — duplicate a step
- `delete_step` — permanent delete step
- `save_meta_settings` — save step settings
- `update_step_title` — rename a step

### Analytics (admin-core/ajax/flows-stats.php)
- `get_all_flows_stats` — earnings, orders by date range, per-flow, recent orders

### Settings (admin-core/ajax/common-settings.php)
- `save_global_settings` — save global plugin settings

### Product/Coupon search (admin-core/ajax/meta-data.php)
- `json_search_products` — search WooCommerce products
- `json_search_coupons` — search WooCommerce coupons

---

## WooCommerce Dependency Note

Checkout, thank-you, optin, email-report, woo-dynamic-flow and analytics modules only load when `WC()` function exists (`$this->is_woo_active`). Abilities from these modules should declare WooCommerce as a requirement in their description.

---

## Phase 0 Status: COMPLETE
