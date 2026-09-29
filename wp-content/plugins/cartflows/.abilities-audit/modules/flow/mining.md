# Phase 1: Ability Mining — Flow (Funnel) Module

**Module:** Flow / Funnel
**Source files:**
- `admin-core/ajax/flows.php`
- `admin-core/api/flows.php` (REST: list flows)
- `admin-core/api/flow-data.php` (REST: get single flow)

---

## Mining Checklist

- [x] Setup/Install: Activation creates CPTs, flushes rewrite rules
- [x] CRUD: list, get, create (via importer), update, delete for flows and steps
- [x] Lifecycle: trash, restore, clone, publish/draft toggle
- [x] Output/Embedding: export as JSON, get permalink
- [x] Analytics/Stats: See analytics module
- [x] Relationships: steps belong to flows (reorder, add, remove)
- [x] Bulk Operations: bulk trash, bulk status update, bulk delete, bulk export
- [x] Configuration: flow meta settings (title, slug, testing mode, layout)

---

## Ability Candidates

### CF-F-01: cartflows/list-flows

- **Label:** List funnels
- **Description:** Returns a paginated list of CartFlows funnels with optional status, search, and date filters. Excludes the store checkout flow. Useful for browsing or selecting funnels.
- **Category:** cartflows
- **Ability category type:** CRUD (Read)
- **Input schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "status": {"type": "string", "enum": ["publish","draft","trash","any"], "default": "publish", "description": "Filter by post status."},
      "search": {"type": "string", "description": "Search term to filter funnels by title."},
      "paged": {"type": "integer", "description": "Page number.", "default": 1},
      "per_page": {"type": "integer", "description": "Funnels per page (max 100).", "default": 10}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "items": {"type": "array", "items": {"type": "object", "properties": {
        "ID": {"type": "integer", "description": "Funnel ID."},
        "post_title": {"type": "string", "description": "Funnel title."},
        "post_status": {"type": "string", "description": "Funnel status."},
        "post_date": {"type": "string", "description": "Creation date."},
        "flow_test_mode": {"type": "boolean", "description": "Whether funnel is in test mode."}
      }}},
      "found_posts": {"type": "integer", "description": "Total matching funnels."},
      "active_flows_count": {"type": "integer", "description": "Total published funnels."},
      "draft_flows_count": {"type": "integer", "description": "Total draft funnels."},
      "pagination": {"type": "object", "properties": {
        "max_pages": {"type": "integer"},
        "paged": {"type": "integer"},
        "found_posts": {"type": "integer"}
      }}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps`
- **Meta annotations:**
  - priority: 1.0
  - readOnlyHint: true
  - destructiveHint: false
  - idempotentHint: true
  - openWorldHint: false
  - mcp.type: resource

---

### CF-F-02: cartflows/get-flow

- **Label:** Get funnel
- **Description:** Returns full data for a single CartFlows funnel by ID, including its ordered steps, settings, and permalink.
- **Category:** cartflows
- **Ability category type:** CRUD (Read)
- **Input schema:**
  ```json
  {
    "type": "object",
    "required": ["id"],
    "properties": {
      "id": {"type": "integer", "description": "The funnel (flow) ID."}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "id": {"type": "integer"},
      "title": {"type": "string"},
      "slug": {"type": "string"},
      "link": {"type": "string", "format": "uri"},
      "status": {"type": "string"},
      "steps": {"type": "array", "items": {"type": "object", "properties": {
        "id": {"type": "integer"},
        "title": {"type": "string"},
        "type": {"type": "string", "description": "Step type: checkout, thankyou, optin, landing."},
        "link": {"type": "string", "format": "uri"}
      }}},
      "options": {"type": "object", "description": "Funnel meta options."},
      "settings_data": {"type": "object", "description": "Funnel settings panel data."}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps`
- **Meta annotations:**
  - priority: 1.0
  - readOnlyHint: true
  - destructiveHint: false
  - idempotentHint: true
  - openWorldHint: false
  - mcp.type: resource

---

### CF-F-03: cartflows/update-flow

- **Label:** Update funnel
- **Description:** Updates a CartFlows funnel's title and/or slug. Use for renaming funnels.
- **Category:** cartflows
- **Ability category type:** CRUD (Update)
- **Input schema:**
  ```json
  {
    "type": "object",
    "required": ["id", "title"],
    "properties": {
      "id": {"type": "integer", "description": "The funnel ID."},
      "title": {"type": "string", "description": "New funnel title."},
      "slug": {"type": "string", "description": "New funnel URL slug (optional)."}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "success": {"type": "boolean"},
      "message": {"type": "string"}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps`
- **Meta annotations:**
  - priority: 2.0
  - readOnlyHint: false
  - destructiveHint: false
  - idempotentHint: true
  - openWorldHint: false
  - mcp.type: tool

---

### CF-F-04: cartflows/publish-flow

- **Label:** Publish funnel
- **Description:** Sets a CartFlows funnel's status to published (active). Also publishes all its steps. Use to activate a funnel so visitors can see it.
- **Category:** cartflows
- **Ability category type:** Lifecycle
- **Input schema:**
  ```json
  {
    "type": "object",
    "required": ["id"],
    "properties": {
      "id": {"type": "integer", "description": "The funnel ID."}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "success": {"type": "boolean"},
      "message": {"type": "string"}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps`
- **Meta annotations:**
  - priority: 2.0
  - readOnlyHint: false
  - destructiveHint: false
  - idempotentHint: true
  - openWorldHint: false
  - mcp.type: tool

---

### CF-F-05: cartflows/unpublish-flow

- **Label:** Unpublish funnel
- **Description:** Sets a CartFlows funnel's status to draft (inactive). Also drafts all its steps. Use to temporarily disable a funnel.
- **Category:** cartflows
- **Ability category type:** Lifecycle
- **Input schema:**
  ```json
  {
    "type": "object",
    "required": ["id"],
    "properties": {
      "id": {"type": "integer", "description": "The funnel ID."}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "success": {"type": "boolean"},
      "message": {"type": "string"}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps`
- **Meta annotations:**
  - priority: 2.0
  - readOnlyHint: false
  - destructiveHint: false
  - idempotentHint: true
  - openWorldHint: false
  - mcp.type: tool

---

### CF-F-06: cartflows/clone-flow

- **Label:** Clone funnel
- **Description:** Creates a full duplicate of a CartFlows funnel, including all steps and their settings. The new funnel is titled "[Original Title] Clone".
- **Category:** cartflows
- **Ability category type:** Lifecycle
- **Input schema:**
  ```json
  {
    "type": "object",
    "required": ["id"],
    "properties": {
      "id": {"type": "integer", "description": "The funnel ID to clone."}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "success": {"type": "boolean"},
      "message": {"type": "string"},
      "new_flow_id": {"type": "integer", "description": "ID of the newly created funnel."},
      "edit_url": {"type": "string", "format": "uri", "description": "Admin edit URL for the new funnel."}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps`
- **Meta annotations:**
  - priority: 2.0
  - readOnlyHint: false
  - destructiveHint: false
  - idempotentHint: false
  - openWorldHint: false
  - mcp.type: tool

---

### CF-F-07: cartflows/trash-flow

- **Label:** Trash funnel
- **Description:** Moves a CartFlows funnel and all its steps to trash. Use to soft-delete a funnel while allowing recovery.
- **Category:** cartflows
- **Ability category type:** Lifecycle
- **Input schema:**
  ```json
  {
    "type": "object",
    "required": ["id"],
    "properties": {
      "id": {"type": "integer", "description": "The funnel ID to trash."}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "success": {"type": "boolean"},
      "message": {"type": "string"}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps`
- **Meta annotations:**
  - priority: 2.0
  - readOnlyHint: false
  - destructiveHint: false
  - idempotentHint: true
  - openWorldHint: false
  - mcp.type: tool

---

### CF-F-08: cartflows/restore-flow

- **Label:** Restore funnel from trash
- **Description:** Restores a trashed CartFlows funnel and all its steps. Use to undo a trash operation.
- **Category:** cartflows
- **Ability category type:** Lifecycle
- **Input schema:**
  ```json
  {
    "type": "object",
    "required": ["id"],
    "properties": {
      "id": {"type": "integer", "description": "The funnel ID to restore."}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "success": {"type": "boolean"},
      "message": {"type": "string"}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps`
- **Meta annotations:**
  - priority: 2.0
  - readOnlyHint: false
  - destructiveHint: false
  - idempotentHint: true
  - openWorldHint: false
  - mcp.type: tool

---

### CF-F-09: cartflows/delete-flow

- **Label:** Permanently delete funnel
- **Description:** Permanently deletes a CartFlows funnel and all its steps. This action CANNOT be undone. Prefer trash-flow unless permanent deletion is explicitly needed.
- **Category:** cartflows
- **Ability category type:** CRUD (Delete)
- **Input schema:**
  ```json
  {
    "type": "object",
    "required": ["id"],
    "properties": {
      "id": {"type": "integer", "description": "The funnel ID to permanently delete."}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "success": {"type": "boolean"},
      "message": {"type": "string"}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps` + option gate
- **Meta annotations:**
  - priority: 3.0
  - readOnlyHint: false
  - destructiveHint: true
  - idempotentHint: false
  - openWorldHint: false
  - mcp.type: tool

---

### CF-F-10: cartflows/reorder-flow-steps

- **Label:** Reorder funnel steps
- **Description:** Changes the order of steps within a CartFlows funnel. Provide the full ordered list of step IDs.
- **Category:** cartflows
- **Ability category type:** Relationships
- **Input schema:**
  ```json
  {
    "type": "object",
    "required": ["flow_id", "step_ids"],
    "properties": {
      "flow_id": {"type": "integer", "description": "The funnel ID."},
      "step_ids": {"type": "array", "items": {"type": "integer"}, "description": "Ordered array of step IDs."}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "success": {"type": "boolean"},
      "steps": {"type": "array", "description": "Updated steps in new order."}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps`
- **Meta annotations:**
  - priority: 2.0
  - readOnlyHint: false
  - destructiveHint: false
  - idempotentHint: true
  - openWorldHint: false
  - mcp.type: tool

---

### CF-F-11: cartflows/export-flow

- **Label:** Export funnel data
- **Description:** Exports one or more CartFlows funnels as a JSON payload, including all steps and settings. Used for migration or backup.
- **Category:** cartflows
- **Ability category type:** Output/Embedding
- **Input schema:**
  ```json
  {
    "type": "object",
    "required": ["flow_ids"],
    "properties": {
      "flow_ids": {"type": "array", "items": {"type": "integer"}, "description": "Array of funnel IDs to export."}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "success": {"type": "boolean"},
      "flows": {"type": "string", "description": "JSON-encoded array of funnel export data."}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps`
- **Meta annotations:**
  - priority: 1.0
  - readOnlyHint: true
  - destructiveHint: false
  - idempotentHint: true
  - openWorldHint: false
  - mcp.type: tool

---

## Step Module Abilities (mined from admin-core/ajax/steps.php and admin-core/api/step-data.php)

### CF-S-01: cartflows/get-step

- **Label:** Get step
- **Description:** Returns full data for a single CartFlows step by ID, including type, settings, options, and page builder edit link.
- **Category:** cartflows
- **Ability category type:** CRUD (Read)
- **Input schema:**
  ```json
  {
    "type": "object",
    "required": ["id"],
    "properties": {
      "id": {"type": "integer", "description": "The step ID."}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "id": {"type": "integer"},
      "title": {"type": "string"},
      "type": {"type": "string", "enum": ["checkout","thankyou","optin","landing"], "description": "Step type."},
      "flow_title": {"type": "string"},
      "view": {"type": "string", "format": "uri", "description": "Frontend permalink."},
      "edit": {"type": "string", "format": "uri", "description": "WP admin edit link."},
      "page_builder_edit": {"type": "string", "format": "uri", "description": "Page builder edit link."},
      "options": {"type": "object", "description": "Step meta options."},
      "settings_data": {"type": "object", "description": "Step settings."}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps`
- **Meta annotations:**
  - priority: 1.0
  - readOnlyHint: true
  - destructiveHint: false
  - idempotentHint: true
  - openWorldHint: false
  - mcp.type: resource

---

### CF-S-02: cartflows/clone-step

- **Label:** Clone step
- **Description:** Creates a duplicate of a CartFlows step within the same funnel, copying all settings and content.
- **Category:** cartflows
- **Ability category type:** Lifecycle
- **Input schema:**
  ```json
  {
    "type": "object",
    "required": ["flow_id", "step_id"],
    "properties": {
      "flow_id": {"type": "integer", "description": "The funnel ID."},
      "step_id": {"type": "integer", "description": "The step ID to clone."}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "success": {"type": "boolean"},
      "message": {"type": "string"},
      "new_step_id": {"type": "integer", "description": "ID of the cloned step."}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps`
- **Meta annotations:**
  - priority: 2.0
  - readOnlyHint: false
  - destructiveHint: false
  - idempotentHint: false
  - openWorldHint: false
  - mcp.type: tool

---

### CF-S-03: cartflows/delete-step

- **Label:** Permanently delete step
- **Description:** Permanently deletes a step from a CartFlows funnel. Cannot be undone.
- **Category:** cartflows
- **Ability category type:** CRUD (Delete)
- **Input schema:**
  ```json
  {
    "type": "object",
    "required": ["flow_id", "step_id"],
    "properties": {
      "flow_id": {"type": "integer", "description": "The funnel ID."},
      "step_id": {"type": "integer", "description": "The step ID to delete."}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "success": {"type": "boolean"},
      "message": {"type": "string"}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps` + option gate
- **Meta annotations:**
  - priority: 3.0
  - readOnlyHint: false
  - destructiveHint: true
  - idempotentHint: false
  - openWorldHint: false
  - mcp.type: tool

---

### CF-S-04: cartflows/update-step-title

- **Label:** Update step title
- **Description:** Renames a CartFlows step.
- **Category:** cartflows
- **Ability category type:** CRUD (Update)
- **Input schema:**
  ```json
  {
    "type": "object",
    "required": ["step_id", "title"],
    "properties": {
      "step_id": {"type": "integer", "description": "The step ID."},
      "title": {"type": "string", "description": "New step title."}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "success": {"type": "boolean"},
      "message": {"type": "string"}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps`
- **Meta annotations:**
  - priority: 2.0
  - readOnlyHint: false
  - destructiveHint: false
  - idempotentHint: true
  - openWorldHint: false
  - mcp.type: tool

---

## Analytics Module Abilities (from admin-core/ajax/flows-stats.php)

### CF-A-01: cartflows/get-flow-stats

- **Label:** Get funnel analytics
- **Description:** Returns revenue and order analytics for CartFlows funnels within a date range. Optionally filter by a specific funnel. Requires WooCommerce. Returns totals, orders by date, revenue by date, and recent orders.
- **Category:** cartflows
- **Ability category type:** Analytics/Stats
- **Input schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "date_from": {"type": "string", "format": "date", "description": "Start date (YYYY-MM-DD)."},
      "date_to": {"type": "string", "format": "date", "description": "End date (YYYY-MM-DD)."},
      "flow_id": {"type": "integer", "description": "Filter by funnel ID. 0 = all funnels.", "default": 0}
    }
  }
  ```
- **Output schema:**
  ```json
  {
    "type": "object",
    "properties": {
      "flow_stats": {"type": "object", "description": "Earnings totals, orders by date, revenue by date."},
      "recent_orders": {"type": "array", "items": {"type": "object", "properties": {
        "order_id": {"type": "integer"},
        "customer_name": {"type": "string"},
        "customer_email": {"type": "string", "format": "email"},
        "order_total": {"type": "string"},
        "order_status": {"type": "string"},
        "order_date": {"type": "string"}
      }}}
    }
  }
  ```
- **Permission:** `cartflows_manage_flows_steps`
- **Meta annotations:**
  - priority: 1.0
  - readOnlyHint: true
  - destructiveHint: false
  - idempotentHint: true
  - openWorldHint: false
  - mcp.type: resource

---

## Abilities NOT Mined (excluded rationale)

| Operation | Reason excluded |
|-----------|----------------|
| `save_flow_meta_settings` | Too complex/broad input (serialized form fields); scope too wide for a clean ability |
| `save_meta_settings` (step) | Same — broad serialized form post data; not AI-constructible cleanly |
| `trash_flows_in_bulk` | Covered by `trash-flow` with a loop; separate bulk ability has limited AI utility |
| `update_flow_post_status` (bulk) | Similar to bulk trash |
| `delete_flows_permanently` (bulk) | Destructive bulk; high risk, low incremental value over single delete |
| `export_flows_in_bulk` | Folded into `export-flow` which accepts array of IDs |
| `hide_instant_checkout_notice` | UI-only operation, not useful for AI agents |
| `update_store_checkout_status` | Pro-adjacent, settings-level toggle |
| `json_search_products` | Too narrow; covered by WooCommerce abilities |
| `json_search_coupons` | Same |
| `save_global_settings` | Too broad; serialized form data |

---

## Phase 1 Status: COMPLETE
