# Pro Plugin Integration

Modern Cart Starter (`modern-cart`) is the free version of Modern Cart. The Pro plugin (`modern-cart-woo`) adds premium features. This page documents how the two plugins interact and how the version compatibility gate works.

---

## Plugin Identifiers

| | Free (Starter) | Pro |
|---|---|---|
| Plugin slug | `modern-cart` | `modern-cart-woo` |
| Main file | `modern-cart/modern-cart.php` | `modern-cart-woo/modern-cart-woo.php` |
| Namespace | `ModernCart\` | (separate namespace in Pro) |
| Constant prefix | `MODERNCART_` | `MODERNCART_PRO_` |
| Active constant | `MODERNCART_VER` | `MODERNCART_PRO_FILE` |

---

## Version Compatibility Gate

The Starter plugin checks Pro compatibility before initialising. This happens at the very top of `modern-cart.php`, before constants are defined.

### Check Function

```php
function moderncart_is_safe_to_init(): bool {
    $pro_file = WP_PLUGIN_DIR . '/modern-cart-woo/modern-cart-woo.php';

    if ( ! file_exists( $pro_file ) ) {
        return true;  // Pro not installed — safe to proceed
    }

    $pro_data    = get_file_data( $pro_file, ['Version' => 'Version'], 'plugin' );
    $pro_version = $pro_data['Version'];

    return version_compare( $pro_version, '1.2.0', '>=' );
}
```

### Incompatibility Handling

If `moderncart_is_safe_to_init()` returns `false`:

1. An admin notice is shown:
   > **Modern Cart for WooCommerce: Update Required**
   > You're using an older version of **Modern Cart for WooCommerce**. Please update to version **1.2.0 or higher** so it works smoothly with **Modern Cart Starter**.

2. Execution stops with `return` — no constants are defined, no classes are loaded, no frontend output.

This prevents PHP fatal errors from API mismatches between the Starter and Pro plugins.

---

## Pro Status Detection

`Helper::get_pro_status()` provides runtime status for the admin UI:

```php
public static function get_pro_status(): string {
    $pro_file = WP_PLUGIN_DIR . '/modern-cart-woo/modern-cart-woo.php';

    if ( ! file_exists( $pro_file ) ) {
        return 'not-installed';
    }

    if ( defined( 'MODERNCART_PRO_FILE' ) ) {
        return 'active';  // Pro defines this constant on load
    }

    return 'inactive';  // File exists but plugin not activated
}
```

| Return | Meaning |
|--------|---------|
| `'not-installed'` | Pro plugin files not present on server |
| `'active'` | Pro is installed and activated |
| `'inactive'` | Pro is installed but deactivated |

This value is passed to the React admin app via `window.moderncart_settings.proStatus` and used to:
- Show/hide Pro-only feature labels
- Display upgrade prompts for upsell features
- Hide or disable Pro settings panels

---

## CartFlows Integration

When CartFlows is active, Modern Cart adjusts the "Edit Cart" button visibility:

```php
'is_needed_edit_cart' => ! function_exists( '_get_wcf_step_id' ) && 'astra' !== get_template()
    ? false
    : true,
```

- `_get_wcf_step_id()` is a CartFlows function present when CartFlows is active
- When CartFlows is active **or** Astra is the active theme, the Edit Cart button is shown
- Otherwise it is hidden (as it's redundant without the CartFlows funnel context)

---

## CPSW (CartFlows Stripe) Express Checkout Integration

Modern Cart integrates with the CartFlows Stripe express checkout plugin (`cpsw`):

| Filter/Hook | Callback | Effect |
|-------------|----------|--------|
| `cpsw_express_checkout_selected_location_status` | Returns `true` | Enables express checkout button inside the cart drawer |
| `cpsw_express_checkout_allow_custom_pages` | Returns `true` | Shows express checkout on all pages when Modern Cart is enabled |
| `cpsw_payment_request_button_before` | Renders "OR" separator | Adds visual separation before the express checkout button |

The express checkout area is hidden via inline CSS if `enable_express_checkout` is `false`:

```css
.moderncart-slide-out-footer #cpsw-payment-request-wrapper {
    display: none !important;
}
```

---

## Custom Price API

Pro (and third-party code) can inject custom prices for cart items by adding a `custom_price` key to the cart item:

```php
// Example: set a custom price for a cart item
add_filter( 'woocommerce_add_cart_item_data', function( $cart_item_data, $product_id ) {
    $cart_item_data['custom_price'] = 9.99;
    return $cart_item_data;
}, 10, 2 );
```

`Slide_Out::set_custom_prices()` hooks into `woocommerce_before_calculate_totals` (priority 10) and applies these prices:

```php
foreach ( $cart->get_cart() as $cart_item ) {
    if ( isset( $cart_item['custom_price'] ) ) {
        $cart_item['data']->set_price( $cart_item['custom_price'] );
    }
}
```

The per-item price display in `moderncart_cart_item_price()` also reads `custom_price` and adjusts the sale percentage calculation accordingly.

---

## Pro Extension Points

The following filters and hooks are designed for Pro to hook into:

| Hook | Type | Use |
|------|------|-----|
| `moderncart_loaded` | action | Hook in early, before `plugins_loaded` |
| `moderncart_default_settings` | filter | Add Pro setting defaults to all groups |
| `moderncart_settings_admin_localize_script` | filter | Inject Pro data into the React admin app |
| `moderncart_admin_version_badge_info` | filter | Change badge from "Free" to "Pro" |
| `moderncart_cpsw_plugin_action_links` | filter | Add Pro-specific plugin page links |
| `moderncart_modal_slide_out_classes` | filter | Add Pro CSS classes to the drawer |
| `moderncart_slide_out_classes` | filter | Add Pro layout classes to the drawer inner |
| `moderncart_override_is_global_enabled` | filter | Override global enable logic for Pro features |
| `moderncart_order_summary_style` | filter | Set Pro-specific order summary style |

---

## Companion Plugin Installation

During onboarding, the wizard offers to install companion plugins. `Helper::install_wordpress_plugins()` handles this:

1. Checks if plugin is already installed (by slug prefix match against `get_plugins()`)
2. Fetches plugin info from WordPress.org via `plugins_api()`
3. Downloads and installs via `Plugin_Upgrader`
4. Activates the installed plugin via `activate_plugin()`

Default companion plugins offered in onboarding:

| Slug | Plugin |
|------|--------|
| `cartflows` | CartFlows (free) |
| `woo-cart-abandonment-recovery` | Cart Abandonment Recovery for WooCommerce |
| `sureforms` | SureForms |
| `surerank` | SureRank |
