# Architecture Overview

This page describes how the Modern Cart plugin boots, how classes are loaded, and how the major components relate to each other.

---

## Boot Sequence

```
WordPress loads plugins
    └── modern-cart.php
           ├── moderncart_is_safe_to_init()   ← Pro version check
           ├── Define MODERNCART_* constants
           ├── require inc/functions.php       ← Global template helpers
           └── require plugin-loader.php
                   └── Plugin_Loader::get_instance()
                          ├── spl_autoload_register()
                          ├── register_activation_hook → activate()
                          ├── add_action('admin_init')  → redirect_to_onboarding()
                          ├── add_action('init')        → save_version_info()
                          ├── add_action('plugins_loaded') → load_classes()
                          └── add_filter('plugin_action_links_*') → action_links()

plugins_loaded fires:
    └── Plugin_Loader::load_classes()
           ├── Bail if WooCommerce not active
           ├── add_action('before_woocommerce_init') → declare_woo_hpos_compatibility()
           ├── if is_admin(): Admin_Menu::get_instance()
           ├── Scripts::get_instance()
           ├── Floating::get_instance()
           ├── Slide_Out::get_instance()
           ├── Slide_Out_Ajax::get_instance()
           └── Floating_Ajax::get_instance()
```

---

## Class Hierarchy

```
ModernCart\
├── Plugin_Loader                          (plugin-loader.php)
│
├── Inc\
│   ├── Traits\
│   │   └── Get_Instance                  (inc/traits/get-instance.php)
│   │
│   ├── Cart                              (inc/cart.php)
│   │   ├── get_option()
│   │   ├── is_global_enabled()
│   │   ├── get_cart_theme_style()
│   │   ├── get_subtotal_html()
│   │   ├── get_discount_html()
│   │   ├── get_shipping_html()
│   │   ├── get_tax_html()
│   │   ├── get_total_html()
│   │   ├── get_free_shipping_amount()
│   │   └── render_free_shipping_bar()
│   │
│   ├── Slide_Out extends Cart            (inc/slide-out.php)
│   │   ├── slide_out()                  → renders modal wrapper
│   │   ├── render_header()
│   │   ├── render_contents()            → iterates cart items
│   │   ├── render_footer()
│   │   ├── render_coupon_form()
│   │   ├── render_coupon_removal()
│   │   ├── render_totals()
│   │   ├── render_empty_cart_recommendations()
│   │   ├── get_recommendations()
│   │   ├── get_empty_cart_recommendations()
│   │   ├── render_quantity_selectors()
│   │   └── get_product_name()
│   │
│   ├── Slide_Out_Ajax extends Slide_Out  (inc/slide-out-ajax.php)
│   │   ├── add_to_cart()
│   │   ├── apply_coupon()
│   │   ├── remove_coupon()
│   │   ├── update_cart()
│   │   ├── remove_product()
│   │   └── refresh_slide_out_cart()
│   │
│   ├── Floating extends Cart             (inc/floating.php)
│   │   └── floating_cart()              → renders floating button
│   │
│   ├── Floating_Ajax extends Floating    (inc/floating-ajax.php)
│   │   └── refresh_floating_cart()
│   │
│   ├── Scripts extends Cart              (inc/scripts.php)
│   │   ├── enqueue_scripts()
│   │   └── dynamic_styles()             → inlines CSS custom properties
│   │
│   └── Helper                           (inc/helper.php)
│       ├── get_defaults()               → all setting defaults + schema
│       ├── get_option()                 → DB value merged with defaults
│       ├── is_cart_empty()
│       ├── is_astra_active()
│       ├── get_astra_color_vars()
│       ├── get_compatible_colors()
│       ├── get_cart_icons()             → SVG icon set (10 icons)
│       ├── get_knowledge_base()         → fetched from cartflows.com API
│       ├── get_pro_status()
│       ├── get_cart_count()
│       ├── is_admin_onboarding_screen()
│       ├── install_wordpress_plugins()
│       ├── set_nocache_headers()
│       ├── is_maintenance_mode()
│       ├── get_allowed_tags_kses()
│       └── setting_doc_link()
│
└── Admin_Core\
    ├── Admin_Menu                        (admin-core/admin-menu.php)
    │   ├── settings_page()
    │   ├── render()
    │   ├── settings_page_scripts()
    │   ├── moderncart_update_settings()
    │   ├── update_settings()
    │   ├── sanitize_data()
    │   ├── fetch_whats_new()
    │   ├── complete_onboarding()
    │   ├── get_onboarding_defaults()
    │   └── map_onboarding_key_to_original_key()
    │
    └── Inc\
        └── Settings_Fields               (admin-core/inc/settings-fields.php)
            ├── get_tabs()
            ├── get_fields()
            └── get_icon_svg()
```

---

## Autoloader

The custom SPL autoloader in `Plugin_Loader::autoload()` maps class names to file paths:

**Rule:** Namespace `ModernCart\` is stripped, `\` → directory separator, camelCase → kebab-case, lowercase.

| Class | File |
|-------|------|
| `ModernCart\Inc\Helper` | `inc/helper.php` |
| `ModernCart\Inc\SlideOut` | `inc/slide-out.php` |
| `ModernCart\Inc\SlideOutAjax` | `inc/slide-out-ajax.php` |
| `ModernCart\Inc\FloatingAjax` | `inc/floating-ajax.php` |
| `ModernCart\Inc\Traits\GetInstance` | `inc/traits/get-instance.php` |
| `ModernCart\Admin_Core\AdminMenu` | `admin-core/admin-menu.php` |
| `ModernCart\Admin_Core\Inc\SettingsFields` | `admin-core/inc/settings-fields.php` |

The autoloader only acts on classes prefixed with `ModernCart\` — all other classes are ignored.

---

## Singleton Pattern

Every feature class uses the `Get_Instance` trait:

```php
use ModernCart\Inc\Traits\Get_Instance;

class MyClass {
    use Get_Instance;
    // ...
}

// Usage:
$instance = MyClass::get_instance();
```

`Plugin_Loader` implements its own static `$instance` without the trait.

---

## WooCommerce Integration Points

| Hook | Class | Purpose |
|------|-------|---------|
| `wp_footer` | `Floating` | Outputs floating cart button HTML |
| `wp_footer` | `Slide_Out` | Outputs slide-out drawer HTML |
| `wp_enqueue_scripts` | `Scripts` | Enqueues `cart.css`, `cart.js`, dynamic CSS |
| `before_woocommerce_init` | `Plugin_Loader` | Declares HPOS compatibility |
| `woocommerce_before_calculate_totals` | `Slide_Out` | Applies custom per-item prices |
| AJAX actions | `Slide_Out_Ajax` | Cart CRUD operations |
| AJAX actions | `Floating_Ajax` | Floating button refresh |

---

## Admin Integration Points

| Hook | Class | Purpose |
|------|-------|---------|
| `admin_menu` | `Admin_Menu` | Registers submenu under WooCommerce |
| `admin_enqueue_scripts` | `Admin_Menu` | Enqueues React settings app |
| `wp_ajax_moderncart_update_settings` | `Admin_Menu` | Saves settings to `wp_options` |
| `wp_ajax_moderncart_fetch_whats_new` | `Admin_Menu` | Proxies RSS feed from cartflows.com |
| `wp_ajax_moderncart_complete_onboarding` | `Admin_Menu` | Completes onboarding wizard |

---

## Data Flow: Settings Save

```
React admin UI
    └── POST admin-ajax.php?action=moderncart_update_settings
            └── Admin_Menu::moderncart_update_settings()
                    ├── check_ajax_referer('moderncart_update_settings', 'security')
                    ├── current_user_can('manage_options')
                    └── Admin_Menu::update_settings($data, $key)
                            ├── json_decode($settings_data)
                            ├── Admin_Menu::sanitize_data($key, $data)  ← type-safe
                            ├── wp_parse_args($data, $defaults)
                            └── update_option($key, $data)
```

---

## Data Flow: Cart AJAX

```
Frontend JS (cart.js)
    └── POST admin-ajax.php?action=moderncart_<action>
            ├── wp_verify_nonce($_POST['moderncart_nonce'], 'moderncart_ajax_nonce')
            └── Slide_Out_Ajax::<action>()
                    ├── WC()->cart operations
                    ├── ob_start()
                    ├── moderncart_get_template_part('shop/slide-out-inner', ...)
                    └── wp_send_json(['content' => $result])

Frontend JS receives JSON → replaces innerHTML of cart drawer
```
