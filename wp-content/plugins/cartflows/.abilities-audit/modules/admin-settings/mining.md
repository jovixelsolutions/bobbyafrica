# Admin/Settings Module — Ability Mining

**Date:** 2026-03-04
**Source files:**
- `admin-core/ajax/common-settings.php`
- `classes/class-cartflows-helper.php`

---

## Settings Store Summary

| Option Key | Content |
|------------|---------|
| `_cartflows_common` | General settings: page builder, global checkout, override flags, indexing |
| `_cartflows_permalink` | URL slug settings for flows and steps |
| `_cartflows_facebook` | Facebook Pixel tracking IDs and event toggles |
| `_cartflows_google_analytics` | GA4 tracking ID and event toggles |
| `_cartflows_google_ads` | Google Ads conversion ID, label, event toggles |
| `_cartflows_tiktok` | TikTok Pixel ID and event toggles |
| `_cartflows_pinterest` | Pinterest Tag ID and event toggles |
| `_cartflows_snapchat` | Snapchat Pixel ID and event toggles |
| `_cartflows_roles` | User role capability mappings |
| `cartflows_delete_plugin_data` | Scalar: whether to delete data on uninstall |
| `cartflows_stats_report_emails` | Scalar: whether email reports enabled |
| `cartflows_stats_report_email_ids` | Scalar: newline-delimited email list |

---

## Mined Ability Candidates

---

### Candidate 1: `cartflows/get-general-settings`

**Category:** Configuration
**Label:** Get general settings
**Description:** Returns CartFlows general settings: default page builder, global checkout page, and display/override flags.

**Input schema:**
```json
{
  "type": "object",
  "properties": {}
}
```
(No input required — returns all general settings.)

**Output schema:**
```json
{
  "type": "object",
  "properties": {
    "default_page_builder": { "type": "string", "description": "Active page builder slug." },
    "global_checkout": { "type": "string", "description": "Global checkout page ID or empty string." },
    "override_global_checkout": { "type": "string", "description": "enable or disable." },
    "override_store_order_pay": { "type": "string", "description": "enable or disable." },
    "disallow_indexing": { "type": "string", "description": "enable or disable." }
  }
}
```

**Permission:** `cartflows_manage_flows_steps`
**Meta:** `readOnlyHint: true`, `idempotentHint: true`, `destructiveHint: false`, `priority: 1.0`

---

### Candidate 2: `cartflows/get-permalink-settings`

**Category:** Configuration
**Label:** Get permalink settings
**Description:** Returns the CartFlows URL slug configuration for flows and steps.

**Input schema:** No properties required.

**Output schema:**
```json
{
  "type": "object",
  "properties": {
    "permalink": { "type": "string", "description": "Step URL base slug." },
    "permalink_flow_base": { "type": "string", "description": "Flow URL base slug." },
    "permalink_structure": { "type": "string", "description": "Permalink structure option." }
  }
}
```

**Permission:** `cartflows_manage_flows_steps`
**Meta:** `readOnlyHint: true`, `idempotentHint: true`, `destructiveHint: false`, `priority: 1.0`

---

### Candidate 3: `cartflows/get-integration-settings`

**Category:** Configuration
**Label:** Get integration settings
**Description:** Returns the active pixel and analytics integration configurations: Facebook, Google Analytics, Google Ads, TikTok, Pinterest, Snapchat. Each sub-group contains the pixel/tracking ID and event toggle settings.

**Input schema:**
```json
{
  "type": "object",
  "properties": {
    "integration": {
      "type": "string",
      "enum": ["facebook", "google_analytics", "google_ads", "tiktok", "pinterest", "snapchat", "all"],
      "default": "all",
      "description": "Which integration group to return. Defaults to all."
    }
  }
}
```

**Output schema:**
```json
{
  "type": "object",
  "properties": {
    "facebook": { "type": "object", "description": "Facebook Pixel settings." },
    "google_analytics": { "type": "object", "description": "Google Analytics settings." },
    "google_ads": { "type": "object", "description": "Google Ads settings." },
    "tiktok": { "type": "object", "description": "TikTok Pixel settings." },
    "pinterest": { "type": "object", "description": "Pinterest Tag settings." },
    "snapchat": { "type": "object", "description": "Snapchat Pixel settings." }
  }
}
```

**Permission:** `cartflows_manage_flows_steps`
**Meta:** `readOnlyHint: true`, `idempotentHint: true`, `destructiveHint: false`, `priority: 1.0`

---

### Candidate 4: `cartflows/update-general-settings`

**Category:** Configuration
**Label:** Update general settings
**Description:** Updates CartFlows general settings: default page builder and global checkout page ID. Use to switch the active page builder or change the global checkout.

**Input schema:**
```json
{
  "type": "object",
  "properties": {
    "default_page_builder": {
      "type": "string",
      "enum": ["elementor", "gutenberg", "divi", "beaver-builder", "bricks-builder"],
      "description": "The page builder to set as default."
    },
    "global_checkout": {
      "type": "string",
      "description": "Post ID of the global checkout flow, or empty string to unset."
    },
    "override_global_checkout": {
      "type": "string",
      "enum": ["enable", "disable"],
      "description": "Whether to override the WooCommerce checkout with the CartFlows global checkout."
    },
    "disallow_indexing": {
      "type": "string",
      "enum": ["enable", "disable"],
      "description": "Whether to block search engine indexing of CartFlows steps."
    }
  }
}
```

**Output schema:**
```json
{
  "type": "object",
  "properties": {
    "message": { "type": "string", "description": "Result message." },
    "settings": { "type": "object", "description": "Updated settings values." }
  }
}
```

**Permission:** `cartflows_manage_flows_steps` + option gate `cartflows_abilities_api_write`
**Meta:** `readOnlyHint: false`, `idempotentHint: true`, `destructiveHint: false`, `priority: 2.0`

---

### Candidate 5: `cartflows/get-store-checkout`

**Category:** Configuration / Relationships
**Label:** Get store checkout flow
**Description:** Returns the ID and details of the flow configured as the CartFlows global store checkout. Returns null if no store checkout is configured.

**Input schema:** No properties required.

**Output schema:**
```json
{
  "type": "object",
  "properties": {
    "flow_id": { "type": "integer", "description": "The store checkout flow ID, or 0 if not set." },
    "flow_title": { "type": "string", "description": "The store checkout flow title." },
    "url_edit": { "type": "string", "format": "uri", "description": "Admin edit URL." },
    "is_configured": { "type": "boolean", "description": "Whether a store checkout flow is configured." }
  }
}
```

**Permission:** `cartflows_manage_flows_steps`
**Meta:** `readOnlyHint: true`, `idempotentHint: true`, `destructiveHint: false`, `priority: 1.0`

---

### Candidate 6: `cartflows/update-permalink-settings`

**Category:** Configuration
**Label:** Update permalink settings
**Description:** Updates CartFlows URL slug settings for flows and steps. Forces a permalink refresh after saving.

**Input schema:**
```json
{
  "type": "object",
  "properties": {
    "permalink": { "type": "string", "description": "Step URL base slug." },
    "permalink_flow_base": { "type": "string", "description": "Flow URL base slug." },
    "permalink_structure": { "type": "string", "description": "Permalink structure option (empty for default)." }
  }
}
```

**Output schema:**
```json
{
  "type": "object",
  "properties": {
    "message": { "type": "string", "description": "Result message." },
    "permalink": { "type": "string", "description": "Saved step slug." },
    "permalink_flow_base": { "type": "string", "description": "Saved flow slug." }
  }
}
```

**Permission:** `cartflows_manage_flows_steps` + option gate `cartflows_abilities_api_write`
**Meta:** `readOnlyHint: false`, `idempotentHint: true`, `destructiveHint: false`, `priority: 2.0`

---

### Candidate 7 (REJECTED pre-review): `cartflows/save-user-role-settings`

**Reasoning for pre-rejection:** Modifying WordPress user role capabilities is a highly destructive, irreversible administrative action. The input would require knowing role slugs and the specific `access_to_cartflows` / `access_to_flows_and_step` key system — complex input an AI would likely misconstruct. Risk of locking out users from CartFlows entirely. This is an internal admin operation with no clear AI agent trigger scenario. Pre-rejected before expert review.

---

### Candidate 8 (REJECTED pre-review): `cartflows/regenerate-css`

**Reasoning for pre-rejection:** This is a maintenance utility that updates a version timestamp to bust CSS caches. It has no input, produces only a success message, and offers no actionable output. The trigger is unclear — an AI would not know when CartFlows CSS needs to be regenerated. Too narrow and internal-only. Pre-rejected.

---

### Candidate 9 (REJECTED pre-review): `cartflows/get-debug-settings`

**Reasoning for pre-rejection:** The debug settings only contain `allow_minified_files` (enable/disable). This is a developer-facing toggle with no AI agent utility and no meaningful trigger scenario. Too narrow and internal. Pre-rejected.

---

## Summary of Candidates Proceeding to Expert Review

| # | Ability Name | Category |
|---|-------------|---------|
| 1 | `cartflows/get-general-settings` | Configuration |
| 2 | `cartflows/get-permalink-settings` | Configuration |
| 3 | `cartflows/get-integration-settings` | Configuration |
| 4 | `cartflows/update-general-settings` | Configuration |
| 5 | `cartflows/get-store-checkout` | Configuration / Relationships |
| 6 | `cartflows/update-permalink-settings` | Configuration |
