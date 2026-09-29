# REST API Reference

CartFlows exposes a REST API under the `cartflows/v1` namespace. All endpoints are admin-only — they require authentication and the appropriate CartFlows capability.

## Base URL

```
/wp-json/cartflows/v1/
```

## Authentication

All endpoints require:
1. A valid WordPress nonce sent as the `X-WP-Nonce` header
2. The appropriate CartFlows capability (see each endpoint)

In the React apps, `@wordpress/api-fetch` handles authentication automatically:

```js
import apiFetch from '@wordpress/api-fetch';

apiFetch.use( apiFetch.createNonceMiddleware( wcfEditorAppData.nonce ) );
```

## Custom Capabilities

CartFlows registers two custom WordPress capabilities:

| Capability | Purpose |
|-----------|---------|
| `cartflows_manage_flows_steps` | Create, edit, and delete flows and steps |
| `cartflows_manage_settings` | Edit global plugin settings |

These are assigned to WordPress roles via the **User Role Manager** in global settings.

---

## Endpoints

### 1. List Flows

Retrieve a paginated list of all flows/funnels.

```
POST /wp-json/cartflows/v1/admin/flows/
```

**Permission:** `cartflows_manage_flows_steps`

**Request body (JSON):**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `status` | string | `publish` | Filter by post status (`publish`, `draft`, `trash`, `any`) |
| `page` | int | `1` | Page number |
| `per_page` | int | `10` | Items per page |
| `search` | string | `""` | Search term for flow title |
| `start_date` | string | — | Start date for date range filter |
| `end_date` | string | — | End date for date range filter |
| `test_mode` | bool | `false` | Include test mode flows |

**Response:**

```json
{
  "success": true,
  "data": {
    "flows": [ /* Array of flow objects */ ],
    "total": 42,
    "flow_counts": {
      "active": 10,
      "draft": 5,
      "trash": 2
    }
  }
}
```

**Flow object fields:**

| Field | Type | Description |
|-------|------|-------------|
| `id` | int | Flow post ID |
| `title` | string | Flow title |
| `slug` | string | Flow URL slug |
| `status` | string | Post status (publish/draft/trash) |
| `steps` | array | Array of step summaries |
| `revenue` | string | Revenue data (filtered via `cartflows_flow_revenue`) |

---

### 2. Get Flow Data

Retrieve complete data for a single flow including all steps and settings.

```
GET /wp-json/cartflows/v1/admin/flow-data/{id}
```

**Permission:** `cartflows_manage_flows_steps`

**URL parameters:**

| Parameter | Description |
|-----------|-------------|
| `id` | Flow post ID |

**Response:**

```json
{
  "success": true,
  "data": {
    "id": 123,
    "title": "My Sales Funnel",
    "slug": "my-sales-funnel",
    "link": "https://example.com/flows/my-sales-funnel",
    "status": "publish",
    "steps": [ /* Array of step objects */ ],
    "meta": { /* Flow meta options */ },
    "settings": { /* Flow settings */ }
  }
}
```

---

### 3. Get Step Data

Retrieve complete data for a single step including all settings, tabs, and configuration.

```
GET /wp-json/cartflows/v1/admin/step-data/{id}
```

**Permission:** `cartflows_manage_flows_steps`

**URL parameters:**

| Parameter | Description |
|-----------|-------------|
| `id` | Step post ID |

**Response:**

```json
{
  "success": true,
  "data": {
    "id": 456,
    "title": "Checkout Step",
    "type": "checkout",
    "flow_id": 123,
    "flow_title": "My Sales Funnel",
    "tabs": { /* Tab configuration */ },
    "settings": { /* Step settings */ },
    "page_settings": { /* Page settings */ },
    "design_settings": { /* Design settings */ },
    "meta": { /* Step post meta */ },
    "links": {
      "view": "https://example.com/flows/my-sales-funnel/checkout-step",
      "edit": "https://example.com/wp-admin/...",
      "page_builder_edit": "https://example.com/wp-admin/..."
    }
  }
}
```

---

### 4. Get Common Settings

Retrieve global CartFlows settings and their field definitions.

```
GET /wp-json/cartflows/v1/admin/commonsettings/
```

**Permission:** `cartflows_manage_flows_steps`

**Response:**

```json
{
  "success": true,
  "data": {
    "settings": {
      "_cartflows_common": { /* General settings */ },
      "_cartflows_permalink": { /* Permalink settings */ },
      "_cartflows_facebook": { /* Facebook integration */ },
      "_cartflows_google_analytics": { /* GA settings */ },
      "_cartflows_roles": { /* User role settings */ }
    },
    "fields": { /* Field definitions for settings form */ }
  }
}
```

---

### 5. Get Home Page Settings

Retrieve dashboard configuration and visibility settings.

```
GET /wp-json/cartflows/v1/admin/homepage/
```

**Permission:** `cartflows_manage_flows_steps`

**Response:**

```json
{
  "success": true,
  "data": {
    "show_analytics": true,
    "show_quick_actions": true
  }
}
```

---

### 6. Get Setup Checklist

Retrieve setup checklist data for the onboarding flow.

```
POST /wp-json/cartflows/v1/admin/setup-checklist/
```

**Permission:** `cartflows_manage_flows_steps`

**Response:**

```json
{
  "success": true,
  "data": {
    "published_flows_count": 0,
    "first_checkout_step_id": null,
    "first_checkout_step_flow_id": null
  }
}
```

---

## Error Responses

All endpoints return errors in this format:

```json
{
  "code": "rest_forbidden",
  "message": "Sorry, you are not allowed to do that.",
  "data": { "status": 403 }
}
```

| HTTP Status | Meaning |
|-------------|---------|
| `200` | Success |
| `400` | Bad request (invalid parameters) |
| `401` | Unauthenticated (missing/invalid nonce) |
| `403` | Forbidden (insufficient capability) |
| `404` | Not found |
| `500` | Server error |

---

## Extending the API

CartFlows provides filters to extend API responses:

```php
// Add data to the flows list response
add_filter( 'cartflows_admin_flows_step_data', function( $steps ) {
    // Modify step data here
    return $steps;
} );

// Add fields to global settings
add_filter( 'cartflows_admin_global_data_options', function( $options ) {
    $options['my_custom_setting'] = get_option( 'my_plugin_setting' );
    return $options;
} );
```

---

## Related Pages

- [AJAX-API-Reference](AJAX-API-Reference)
- [WordPress-Hooks-Reference](WordPress-Hooks-Reference)
- [Architecture-Overview](Architecture-Overview)
- [WordPress-Plugin-Structure](WordPress-Plugin-Structure)
