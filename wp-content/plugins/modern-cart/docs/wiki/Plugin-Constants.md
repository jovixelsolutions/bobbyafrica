# Plugin Constants

All `MODERNCART_*` constants are defined in `modern-cart.php` immediately after the Pro version gate passes. They are defined with `if ( ! defined(...) )` guards to allow external override if needed.

---

## Core Path Constants

| Constant | Value | Description |
|----------|-------|-------------|
| `MODERNCART_FILE` | `__FILE__` (absolute path to `modern-cart.php`) | Used as the anchor for all path/URL derivations |
| `MODERNCART_BASE` | `plugin_basename(MODERNCART_FILE)` | Plugin base path, e.g. `modern-cart/modern-cart.php` |
| `MODERNCART_DIR` | `plugin_dir_path(MODERNCART_FILE)` | Absolute filesystem path to plugin root, with trailing slash |
| `MODERNCART_PLUGIN_PATH` | `untrailingslashit(MODERNCART_DIR)` | Plugin root without trailing slash |
| `MODERNCART_URL` | `plugins_url('/', MODERNCART_FILE)` | Public URL to plugin root, with trailing slash |

---

## Version

| Constant | Value | Description |
|----------|-------|-------------|
| `MODERNCART_VER` | `'1.0.7'` | Current plugin version string. Used for script/style versioning and option tracking. |

---

## Options Keys

These constants are the `wp_options` keys used to store all plugin settings.

| Constant | Key | Description |
|----------|-----|-------------|
| `MODERNCART_MAIN_SETTINGS` | `'moderncart_setting'` | General settings (enable/disable, AJAX, free shipping bar, powered-by) |
| `MODERNCART_SETTINGS` | `'moderncart_cart'` | Cart-specific settings (style, coupon, recommendations, titles, labels) |
| `MODERNCART_FLOATING_SETTINGS` | `'moderncart_floating'` | Floating button settings (position, icon, visibility) |
| `MODERNCART_APPEARANCE_SETTINGS` | `'moderncart_appearance'` | Appearance/colour settings (primary colour, header font, alignment) |

---

## External API Constants

| Constant | Default Value | Description |
|----------|---------------|-------------|
| `CARTFLOWS_DOMAIN_URL` | `'https://cartflows.com'` | Base URL for CartFlows API |
| `CARTFLOWS_API_ROUTE` | `'/wp-json/powerful-docs/v1'` | REST API route prefix |
| `CARTFLOWS_DOCS_ENDPOINT` | `'/get-docs'` | Endpoint for knowledge base articles |
| `MODERNCART_ONBOARDING_USER_SUB_WORKFLOW_URL` | `'https://webhook.ottokit.com/ottokit/82404eb3-...'` | OttoKit webhook for onboarding user data submission |

The knowledge base URL is constructed as:
```
CARTFLOWS_DOMAIN_URL + CARTFLOWS_API_ROUTE + CARTFLOWS_DOCS_ENDPOINT
= https://cartflows.com/wp-json/powerful-docs/v1/get-docs
```

---

## Debug Flag

| Constant | Default | Description |
|----------|---------|-------------|
| `MODERNCART_DEBUG` | `false` | When `true`, script version is set to `time() . '-' . MODERNCART_VER` to bust browser cache on every page load. |

To enable during development, define before the plugin loads (e.g., in `wp-config.php`):

```php
define( 'MODERNCART_DEBUG', true );
```

---

## Usage Examples

```php
// Get the plugin directory path
$path = MODERNCART_DIR . 'templates/cart/cart-item-style1.php';

// Get a public asset URL
$url = MODERNCART_URL . 'assets/css/cart.css';

// Get the plugin version
$version = MODERNCART_VER;

// Read main settings from DB
$settings = get_option( MODERNCART_MAIN_SETTINGS, [] );

// Read cart settings from DB
$cart_settings = get_option( MODERNCART_SETTINGS, [] );
```

---

## Admin Page Slug

The admin page is not a constant but is used in multiple places:

```php
'moderncart_settings'  // page slug for add_submenu_page() and URL parameter
```

URL: `/wp-admin/admin.php?page=moderncart_settings`

---

## wp_options Keys (Non-Constant)

| Option Key | Description |
|------------|-------------|
| `moderncart_version` | Array of `{ current, previous }` version strings, updated on `init` |
| `moderncart_is_onboarding_complete` | `'yes'` or `'no'` — prevents onboarding redirect after first run |

Transient:

| Transient Key | TTL | Description |
|---------------|-----|-------------|
| `moderncart_redirect_to_onboarding` | One-time (deleted on use) | Triggers redirect to onboarding after first activation |
| `moderncart_knowledge_base_data` | 12 hours | Cached knowledge base articles from cartflows.com API |
