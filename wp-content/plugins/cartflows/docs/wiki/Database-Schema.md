# Database Schema

CartFlows uses WordPress's standard database tables — no custom tables are created. Data is stored in `wp_posts`, `wp_postmeta`, `wp_options`, and `wp_term_relationships`.

## Custom Post Types

### `cartflows_flow` — Funnel/Flow

A flow is a container that groups related steps into a sales funnel.

| Column | Value | Description |
|--------|-------|-------------|
| `post_type` | `cartflows_flow` | Identifies this as a flow |
| `post_title` | string | Flow display name |
| `post_status` | `publish`/`draft`/`trash` | Flow status |
| `post_name` | string | URL slug |

**Characteristics:**
- Not publicly queryable via the frontend URL
- Shown in the WordPress admin UI
- No archive page
- Steps are stored as separate `cartflows_step` posts linked via taxonomy

### `cartflows_step` — Funnel Step

A step is an individual page within a funnel (checkout, optin, landing, thank you, offer).

| Column | Value | Description |
|--------|-------|-------------|
| `post_type` | `cartflows_step` | Identifies this as a step |
| `post_title` | string | Step display name |
| `post_status` | `publish`/`draft` | Step status |
| `post_content` | string | Page builder content |
| `post_name` | string | URL slug |

**Characteristics:**
- Publicly queryable (has a frontend URL)
- Exposed in the REST API
- Page content is managed by the active page builder (Elementor, Gutenberg, etc.)
- Supports revisions

---

## Taxonomies

Two taxonomies are attached to `cartflows_step`:

### `cartflows_step_type` — Step Type

Classifies what type of step this is.

| Term | Description |
|------|-------------|
| `landing` | Landing / sales page |
| `checkout` | WooCommerce checkout page |
| `optin` | Lead capture form |
| `thankyou` | Post-purchase thank you page |
| `upsell` | Post-purchase upsell offer |
| `downsell` | Post-purchase downsell offer |

### `cartflows_step_flow` — Step's Parent Flow

Links a step to its parent flow. The term slug is the parent flow's post ID.

### `cartflows_step_page_builder` — Page Builder

Tracks which page builder manages this step's content.

| Term | Page Builder |
|------|-------------|
| `gutenberg` | WordPress block editor |
| `elementor` | Elementor |
| `beaver-builder` | Beaver Builder |
| `divi` | Divi |
| `bricks` | Bricks |
| `other` | Default / no page builder |

### `cartflows_flow_category` — Flow Category

Categorises flows (used for template library categorisation).

---

## Post Meta (Flow)

Stored in `wp_postmeta` with `post_id` pointing to a `cartflows_flow` post.

### Core Flow Meta

| Meta Key | Type | Description |
|----------|------|-------------|
| `wcf-steps` | array (serialized) | Ordered array of step post IDs in this flow |
| `wcf-flow-title` | string | Flow title (may duplicate post_title) |
| `wcf-test-mode` | string | `yes`/`no` — test mode enabled |

### Flow Design / Theme Meta

| Meta Key | Type | Description |
|----------|------|-------------|
| `wcf-gcp-primary-color` | string | Primary brand colour (hex) |
| `wcf-gcp-secondary-color` | string | Secondary colour (hex) |
| `wcf-gcp-primary-text-color` | string | Primary text colour (hex) |
| `wcf-gcp-text-color` | string | Field text colour (hex) |
| `wcf-gcp-accent-color` | string | Accent/heading colour (hex) |

---

## Post Meta (Step)

Stored in `wp_postmeta` with `post_id` pointing to a `cartflows_step` post.

### Core Step Meta

| Meta Key | Type | Description |
|----------|------|-------------|
| `wcf-flow-id` | int | Parent flow post ID |
| `wcf-step-type` | string | Step type (checkout, optin, landing, thankyou, upsell, downsell) |
| `wcf-step-note` | string | Internal developer note for this step |
| `wcf-dynamic-css` | string | Generated CSS for this step |
| `wcf-dynamic-css-version` | string | CSS cache version |

### Checkout Step Meta

| Meta Key | Type | Description |
|----------|------|-------------|
| `wcf-checkout-layout` | string | Layout type (e.g., `modern`, `instant`) |
| `wcf-custom-checkout-fields` | string | `yes`/`no` — custom fields enabled |
| `wcf-enable-product-options` | string | `yes`/`no` — product option selection |
| `wcf-order-bump` | array | Order bump product configuration |
| `wcf-pre-checkout-offer` | array | Pre-checkout offer settings |

### Checkout Field Meta

| Meta Key Pattern | Description |
|-----------------|-------------|
| `wcf-{field_name}` | `yes`/`no` — whether the field is enabled |
| `wcf-field-width-{field_name}` | `full`/`half` — field width |
| `wcf-field-required-{field_name}` | `yes`/`no` — field is required |
| `wcf-field-label-{field_name}` | Custom field label text |
| `wcf-field-placeholder-{field_name}` | Custom placeholder text |

---

## Product Post Meta

CartFlows adds meta to `product` posts (WooCommerce):

| Meta Key | Type | Description |
|----------|------|-------------|
| `cartflows_redirect_flow_id` | int | When this product is purchased, redirect to this flow |
| `cartflows_add_to_cart_text` | string | Custom "Add to Cart" button text |

---

## WordPress Options

Stored in `wp_options`. Key options:

### Global Settings

| Option Name | Type | Description |
|-------------|------|-------------|
| `_cartflows_common` | array | General settings: default page builder, store checkout config |
| `_cartflows_permalink` | array | Permalink structure: step slug, flow slug, permalink mode |
| `_cartflows_roles` | array | User role capability assignments |

### Integrations

| Option Name | Type | Description |
|-------------|------|-------------|
| `_cartflows_facebook` | array | Facebook Pixel ID and tracking settings |
| `_cartflows_google_analytics` | array | Google Analytics tracking ID and settings |
| `_cartflows_google_auto_address` | array | Google Places API key and settings |
| `_cartflows_google_ads` | array | Google Ads conversion ID and settings |
| `_cartflows_tiktok` | array | TikTok Pixel settings |
| `_cartflows_pinterest` | array | Pinterest Tag settings |
| `_cartflows_snapchat` | array | Snapchat Pixel settings |

### Reporting & Analytics

| Option Name | Type | Description |
|-------------|------|-------------|
| `cartflows_stats_report_emails` | string | `enable`/`disable` email reports |
| `cartflows_stats_report_email_ids` | string | Newline-separated report recipient emails |
| `cartflows_kb_searches` | array | Last 20 knowledge base search terms |
| `cf_analytics_optin` | string | `yes`/`no` — non-sensitive data tracking |

### System

| Option Name | Type | Description |
|-------------|------|-------------|
| `cartflows-version` | string | Installed CartFlows version |
| `cartflows-assets-version` | int | Timestamp for dynamic CSS cache-busting |
| `cartflows_permalink_refresh` | bool | Flush rewrite rules on next load |
| `cartflows_delete_plugin_data` | string | `yes`/`no` — delete data on uninstall |

---

## Permalink Structure

Steps have a custom permalink format controlled by `_cartflows_permalink`:

```
/{flow-slug}/{step-slug}
```

Default slugs:
- Flow slug base: `wcf` (configurable)
- Step slug base: `wcf-step` (configurable)

Example: `https://example.com/wcf/my-funnel/checkout-step/`

Permalinks are flushed (via `flush_rewrite_rules()`) when the permalink settings are saved.

---

## Related Pages

- [WordPress-Plugin-Structure](WordPress-Plugin-Structure)
- [Feature-Modules](Feature-Modules)
- [Architecture-Overview](Architecture-Overview)
- [WooCommerce-Integration](WooCommerce-Integration)
