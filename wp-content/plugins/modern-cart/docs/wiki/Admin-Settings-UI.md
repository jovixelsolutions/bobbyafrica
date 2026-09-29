# Admin Settings UI

The Modern Cart admin settings page is a React Single Page Application served at **WooCommerce → Modern Cart** (`/wp-admin/admin.php?page=moderncart_settings`).

---

## Registration

`Admin_Menu::settings_page()` registers the submenu via `admin_menu` (priority 99):

```php
add_submenu_page(
    'woocommerce',                      // Parent: under WooCommerce
    'Settings - Modern Cart Woo',       // Page title
    'Modern Cart',                      // Menu label
    'manage_woocommerce',               // Capability required
    'moderncart_settings',              // Slug
    [ $this, 'render' ],                // Callback
    57                                  // Menu position
);
```

The page render callback outputs a single div:

```html
<div class="moderncart-settings" id="moderncart-settings"></div>
```

React mounts into this element.

---

## Script / Style Enqueue

`Admin_Menu::settings_page_scripts()` fires on `admin_enqueue_scripts` and only runs on hook `woocommerce_page_moderncart_settings`.

### Scripts

| Handle | File | Dependencies |
|--------|------|-------------|
| `moderncart_settings` | `admin-core/assets/build/settings.js` | Dependencies from `settings.asset.php` + `updates` |

Localised as `window.moderncart_settings` (see below).

Script translations are loaded from `languages/` via:
```php
wp_set_script_translations( 'moderncart_settings', 'modern-cart', MODERNCART_DIR . 'languages' );
```

### Styles

| Handle | Source |
|--------|--------|
| `moderncart_settings` | `admin-core/assets/build/settings.css` (RTL variant auto-loaded) |
| `wp-components` | WordPress core components styles |
| `moderncart_font` | Google Fonts: Figtree (300, 400, 500, 600) |

---

## Localised Data (`window.moderncart_settings`)

The full data object passed to the React app:

```javascript
{
    ajax_url: "https://example.com/wp-admin/admin-ajax.php",
    proStatus: "not-installed" | "active" | "inactive",
    update_nonce: "<nonce for moderncart_update_settings>",

    // Current setting values (merged with defaults):
    moderncart_setting: { enable_moderncart: "all", enable_powered_by: true, ... },
    moderncart_cart: { cart_style: "slideout", cart_theme_style: "style1", ... },
    moderncart_floating: { floating_cart_position: "bottom-right", ... },
    moderncart_appearance: { primary_color: "#0284C7", ... },

    // Onboarding
    onboarding: {
        inProgress: false,     // true when ?onboarding=1 with valid nonce
        ajaxUrl: "<url for moderncart_complete_onboarding>",
        defaults: { ... }      // Step-by-step defaults (see below)
    },

    // What's New panel
    whats_new_rss_feed: {
        key: "modern-cart",
        label: "Modern Cart",
        url: "<url for moderncart_fetch_whats_new>"
    },

    // Theme compatibility
    theme_colors: ["#046bd2", "#045cb4", ...],  // Astra or fallback palette
    color_default_vars: { primary_color: "#0284C7", ... }, // or Astra CSS vars

    // UI data
    moderncart_cart_icons: ["<svg>...</svg>", ...],  // 10 SVG strings
    knowledge_base: [ { title, link, category, ... } ],  // from cartflows.com API
    settings_tabs: { ... },   // Tab structure from Settings_Fields::get_tabs()
    settings_fields: { ... }, // Field definitions from Settings_Fields::get_fields()
    settings_icons: { ... },  // SVG icon map from Settings_Fields::get_icon_svg()

    // Version badge
    versionBadgeInfo: { label: "Free", title: "1.0.7" }
}
```

Filterable via `moderncart_settings_admin_localize_script`.

---

## Settings Fields Definition

`ModernCart\Admin_Core\Inc\Settings_Fields` (`admin-core/inc/settings-fields.php`) provides:

| Method | Returns | Description |
|--------|---------|-------------|
| `get_tabs()` | array | Tab structure for the settings navigation |
| `get_fields()` | array | Field definitions including labels, options, defaults, doc links |
| `get_icon_svg()` | array | SVG icon strings keyed by identifier |

The `get_fields()` structure drives:
- What options appear in the UI
- What cart theme styles are valid (used by `Cart::get_cart_theme_style()` for validation)
- Embedded documentation links (using `Helper::setting_doc_link()`)

---

## Settings Save Flow

1. React app posts to `admin-ajax.php?action=moderncart_update_settings`
2. `Admin_Menu::moderncart_update_settings()`:
   - `check_ajax_referer('moderncart_update_settings', 'security')`
   - `current_user_can('manage_options')`
   - Collects non-empty POST keys from the four option groups
3. For each key: `Admin_Menu::update_settings($json_string, $key)`
   - JSON decode
   - `Admin_Menu::sanitize_data($key, $data)` — type-safe per-field sanitisation
   - `wp_parse_args($data, $defaults)` — merge with defaults
   - `update_option($key, $data)`
4. Returns `wp_send_json_success` when all keys saved, `wp_send_json_error` on failure

---

## Onboarding Wizard

### Detection

The onboarding wizard renders when `Helper::is_admin_onboarding_screen()` returns `true`:
- `$_GET['page']` === `'moderncart_settings'`
- `$_GET['onboarding']` is set
- `$_GET['nonce']` passes `wp_verify_nonce($nonce, 'moderncart_onboarding_nonce')`

### Fullscreen Mode

When onboarding is detected, inline CSS hides the WordPress toolbar and sidebar:

```css
html.wp-toolbar { padding: 0; }
#wpcontent { margin: 0; padding: 0; }
#wpadminbar, #adminmenumain { display: none; }
```

### Step Defaults

```php
[
    1 => [
        'cart_type'                      => 'slideout',
        'floating_cart_button_position'  => 'bottom-right',
        'enable_free_shipping_bar'       => true,
        'enable_product_recommendations' => false,
    ],
    2 => [
        'user_detail_firstname'    => (current user first name),
        'user_detail_lastname'     => (current user last name),
        'user_detail_email'        => (current user email),
        'optin_newsletter_updates' => true,
        'optin_usage_tracking'     => false,
    ],
    3 => [
        'cartflows'                     => true,
        'woo-cart-abandonment-recovery' => true,
        'sureforms'                     => true,
        'surerank'                      => true,
    ],
]
```

### Key Mapping

Onboarding keys are mapped to actual option keys by `Admin_Menu::map_onboarding_key_to_original_key()`:

| Onboarding Key | Option Group | Setting Key |
|----------------|-------------|-------------|
| `cart_type` | `moderncart_cart` | `cart_type` |
| `floating_cart_button_position` | `moderncart_floating` | `floating_cart_position` |
| `enable_free_shipping_bar` | `moderncart_setting` | `enable_free_shipping_bar` |
| `enable_product_recommendations` | `moderncart_cart` | `recommendation_types` (`true` → `'upsells'`, `false` → `'disabled'`) |
| `enable_moderncart` | `moderncart_setting` | `enable_moderncart` |
| `moderncart_appearance_primary_color` | `moderncart_appearance` | `primary_color` |
| `moderncart_appearance_heading_color` | `moderncart_appearance` | `heading_color` |
| `moderncart_appearance_body_color` | `moderncart_appearance` | `body_color` |
| `moderncart_floating_floating_cart_icon` | `moderncart_floating` | `floating_cart_icon` |
| `moderncart_floating_custom_trigger_selectors` | `moderncart_floating` | `custom_trigger_selectors` |
| `optin_usage_tracking` | `moderncart_cart` | `enable_usage_tracking` |

### Completion

1. Settings data saved to `wp_options` for all four option groups
2. User details POSTed to `MODERNCART_ONBOARDING_USER_SUB_WORKFLOW_URL`
3. Selected companion plugins installed (via WP Plugin API) and activated
4. `update_option('moderncart_is_onboarding_complete', 'yes')` — prevents re-triggering

---

## What's New Panel

The admin includes a "What's New" RSS flyout panel. The RSS feed URL is proxied via `moderncart_fetch_whats_new` AJAX to bypass browser CORS restrictions.

Feed URL: `https://cartflows.com/product/modern-cart/feed/`

The panel CSS ensures it's hidden when closed:
```css
.whats-new-rss-flyout.closed {
    visibility: hidden;
}
```

---

## Knowledge Base Panel

Knowledge base articles are fetched from `https://cartflows.com/wp-json/powerful-docs/v1/get-docs` by `Helper::get_knowledge_base()`, filtered to the `modern-cart-for-woocommerce` category, and cached in a transient (`moderncart_knowledge_base_data`) for 12 hours.

---

## Pro Status Badge

`Helper::get_pro_status()` returns:

| Return | Condition |
|--------|-----------|
| `'not-installed'` | `modern-cart-woo/modern-cart-woo.php` does not exist |
| `'active'` | `MODERNCART_PRO_FILE` constant is defined |
| `'inactive'` | Pro file exists but `MODERNCART_PRO_FILE` not defined |

The React app uses this to show/hide Pro feature labels and upsell prompts.

The version badge is filterable:
```php
apply_filters( 'moderncart_admin_version_badge_info', [
    'label' => 'Free',
    'title' => MODERNCART_VER,
] );
```

---

## Media Library Integration

`wp_enqueue_media()` is called on the settings page to support custom floating cart icon uploads (Pro feature).
