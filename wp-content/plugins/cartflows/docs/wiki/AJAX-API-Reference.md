# AJAX API Reference

CartFlows handles many admin operations via WordPress AJAX (`admin-ajax.php`). All actions use the prefix `cartflows_` and require nonce verification.

## AJAX Pattern

All AJAX handlers extend `AjaxBase` (`admin-core/ajax/ajax-base.php`). The base class:
- Registers `wp_ajax_cartflows_{action}` hooks automatically via `init_ajax_events()`
- Generates and localises nonces for each action
- Provides `AjaxErrors::get_instance()` for standardised error messages

**Request format:**

```js
// JavaScript (jQuery)
$.ajax( {
    url: wcfEditorAppData.ajax_url,
    method: 'POST',
    data: {
        action: 'cartflows_clone_flow',
        security: wcfEditorAppData.nonces.clone_flow,
        flow_id: 123,
    }
} );
```

**Response format:**

```json
// Success
{ "success": true, "data": { ... } }

// Error
{ "success": false, "data": { "messsage": "Error description" } }
```

## Custom Capabilities

| Capability | Required for |
|-----------|-------------|
| `cartflows_manage_flows_steps` | All flow and step operations |
| `cartflows_manage_settings` | Global settings operations |

---

## Flows AJAX (`admin-core/ajax/flows.php`)

**Class:** `CartflowsAdmin\AdminCore\Ajax\Flows`

### `cartflows_update_flow_title`

Update a flow's title.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_update_flow_title` |
| `flow_id` | int | Flow post ID |
| `flow_title` | string | New title |

---

### `cartflows_clone_flow`

Duplicate a flow along with all its steps and post meta.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_clone_flow` |
| `flow_id` | int | Flow post ID to clone |

**Returns:** New flow ID and redirect URL.

---

### `cartflows_delete_flow`

Permanently delete a flow and all associated steps.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_delete_flow` |
| `flow_id` | int | Flow post ID |

---

### `cartflows_trash_flow`

Move a flow to trash (soft delete; recoverable).

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_trash_flow` |
| `flow_id` | int | Flow post ID |

---

### `cartflows_restore_flow`

Restore a trashed flow to its previous status.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_restore_flow` |
| `flow_id` | int | Flow post ID |

---

### `cartflows_reorder_flow_steps`

Reorder steps within a flow (drag-and-drop save).

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_reorder_flow_steps` |
| `flow_id` | int | Flow post ID |
| `steps` | array | Array of step IDs in new order |

---

### `cartflows_trash_flows_in_bulk`

Move multiple flows to trash in a single request.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_trash_flows_in_bulk` |
| `flow_ids` | array | Array of flow post IDs |

---

### `cartflows_update_flow_post_status`

Change the status (publish/draft) for multiple flows.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_update_flow_post_status` |
| `flow_ids` | array | Array of flow post IDs |
| `status` | string | `publish` or `draft` |

---

### `cartflows_delete_flows_permanently`

Permanently delete multiple flows (cannot be undone).

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_delete_flows_permanently` |
| `flow_ids` | array | Array of flow post IDs |

---

### `cartflows_save_flow_meta_settings`

Save flow-level meta data and settings.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_save_flow_meta_settings` |
| `flow_id` | int | Flow post ID |
| `settings` | object | Flow settings key-value pairs |

---

### `cartflows_export_flows_in_bulk`

Export one or more flows as a JSON file for download.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_export_flows_in_bulk` |
| `flow_ids` | array | Array of flow post IDs |

---

### `cartflows_update_status`

Update the status of a flow or step.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_update_status` |
| `id` | int | Post ID (flow or step) |
| `status` | string | New status (`publish`, `draft`) |

---

### `cartflows_update_store_checkout_status`

Enable or disable the global store checkout feature.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_update_store_checkout_status` |
| `status` | string | `enable` or `disable` |

Fires the `cartflows_after_save_store_checkout` action after saving.

---

### `cartflows_hide_instant_checkout_notice`

Dismiss the instant checkout admin notice for the current user.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_hide_instant_checkout_notice` |

---

### `cartflows_get_published_flows`

Retrieve a list of published flows (used in analytics dropdowns).

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_get_published_flows` |

---

## Steps AJAX (`admin-core/ajax/steps.php`)

**Class:** `CartflowsAdmin\AdminCore\Ajax\Steps`

### `cartflows_update_step_title`

Update a step's title.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_update_step_title` |
| `step_id` | int | Step post ID |
| `step_title` | string | New title |

---

### `cartflows_clone_step`

Duplicate a step within a flow (including all post meta).

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_clone_step` |
| `step_id` | int | Step post ID |
| `flow_id` | int | Parent flow post ID |

Note: Cannot clone A/B test steps.

---

### `cartflows_delete_step`

Delete a step from a flow.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_delete_step` |
| `step_id` | int | Step post ID |
| `flow_id` | int | Parent flow post ID |

Note: A/B test steps use the `cartflows_step_delete_ab_test` action for special handling.

---

### `cartflows_save_meta_settings`

Save step meta settings (field configuration, layout, design).

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_save_meta_settings` |
| `step_id` | int | Step post ID |
| `settings` | object | Step settings key-value pairs |

Fires `cartflows_admin_save_step_meta` action after saving. Also triggers dynamic CSS regeneration.

---

## Meta Data AJAX (`admin-core/ajax/meta-data.php`)

**Class:** `CartflowsAdmin\AdminCore\Ajax\MetaData`

### `cartflows_json_search_products`

Search WooCommerce products by title term.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_json_search_products` |
| `term` | string | Product search term |
| `allowed_products` | array | Limit to specific product types |
| `include_products` | array | Include additional product type slugs |
| `exclude_products` | array | Exclude product type slugs |
| `display_stock` | bool | Include stock quantity in response |

**Supported product types (filterable):**

```
simple, variable, variation, subscription,
variable-subscription, subscription_variation, course
```

Filter: `cartflows_supported_product_types_for_search`

---

### `cartflows_json_search_coupons`

Search WooCommerce coupons by title/code.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_json_search_coupons` |
| `term` | string | Coupon code/title search term |

---

## Common Settings AJAX (`admin-core/ajax/common-settings.php`)

**Class:** `CartflowsAdmin\AdminCore\Ajax\CommonSettings`

**Permission:** `cartflows_manage_settings` (stricter than flows)

### `cartflows_save_global_settings`

Save global CartFlows settings. Routes to tab-specific save methods.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_save_global_settings` |
| `setting_tab` | string | `general`, `permalink`, `other`, `user_role_manager`, `integrations` |
| Tab-specific fields | mixed | Depends on `setting_tab` value |

**Tab: `general`**
- `_cartflows_common` — Default page builder, store checkout, other common settings

**Tab: `permalink`**
- `_cartflows_permalink` — Slug structure for flows and steps

**Tab: `other`**
- `cartflows_delete_plugin_data` — Delete on uninstall toggle
- `cartflows_stats_report_emails` — Enable report emails
- `cartflows_stats_report_email_ids` — Report recipient emails (newline-separated)
- `cf_analytics_optin` — Non-sensitive data tracking opt-in

**Tab: `user_role_manager`**
- `_cartflows_roles` — Per-role capability assignments (`access_to_cartflows` or `access_to_flows_and_step`)

**Tab: `integrations`**
- `_cartflows_facebook` — Facebook Pixel settings
- `_cartflows_google_analytics` — Google Analytics settings
- `_cartflows_google_auto_address` — Google Places Autocomplete
- `_cartflows_tiktok` — TikTok Pixel settings
- `_cartflows_pinterest` — Pinterest Tag settings
- `_cartflows_google_ads` — Google Ads settings
- `_cartflows_snapchat` — Snapchat Pixel settings

Fires `cartflows_admin_save_global_settings` action after saving.

---

### `cartflows_regenerate_css_for_steps`

Clear the dynamic CSS cache to force regeneration on next page load.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_regenerate_css_for_steps` |

Updates `cartflows-assets-version` option with current timestamp.

---

### `cartflows_track_kb_search`

Track a knowledge base search term for analytics.

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce: `cartflows_track_kb_search` |
| `search_term` | string | The search term to track |

Stores up to the last 20 searches in `cartflows_kb_searches` option.

---

## Related Pages

- [REST-API-Reference](REST-API-Reference)
- [WordPress-Hooks-Reference](WordPress-Hooks-Reference)
- [WordPress-Plugin-Structure](WordPress-Plugin-Structure)
- [Architecture-Overview](Architecture-Overview)
