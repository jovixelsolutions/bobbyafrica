# Cart Features

This page describes every frontend cart feature: the slide-out drawer, the floating cart button, the free shipping bar, coupon handling, product recommendations, and the express checkout integration.

---

## Feature Enablement Check

All frontend features check `Cart::is_global_enabled()` before rendering anything. This method:

1. Returns `false` if the current request is a REST API nav menu widget render (`/wp-json/wp/v2/widget-types/`)
2. Reads `enable_moderncart` from `MODERNCART_MAIN_SETTINGS`
3. Allows override via `moderncart_override_is_global_enabled` filter
4. Returns `false` when value is `'disabled'`
5. Returns `true` when value is `'all'` AND `is_checkout()` is `false`
6. Returns `true` when `is_shop()`, `is_product()`, or `is_cart()` is `true`

The cart is intentionally **not shown on the checkout page** to avoid UX conflicts.

---

## Slide-Out Cart Drawer

**Class:** `ModernCart\Inc\Slide_Out` (`inc/slide-out.php`)
**Template entry:** `templates/shop/slide-out.php`

### Rendering Sequence

`Slide_Out::slide_out()` fires on `wp_footer` and renders the full modal wrapper:

```
shop/slide-out.php
    └── do_action('moderncart_slide_out_content')
           ├── render_header()           → cart/header-{style}.php
           │      └── do_action('moderncart_slide_out_header_before')
           │             └── render_free_shipping_bar() → cart/free-shipping-bar.php
           ├── render_contents()
           │      ├── cart/cart-item-{theme_style}.php (per item)
           │      └── do_action('moderncart_slide_out_cart_after')
           │             └── render_empty_cart_recommendations() → cart/recommendation-empty.php
           └── render_footer()           → cart/footer.php
                  └── do_action('moderncart_slide_out_footer_content')
                         ├── render_coupon_form()     → cart/coupon-form.php
                         │     └── do_action('moderncart_slide_out_coupon_form_after')
                         │            └── render_coupon_removal()
                         └── render_totals()          → cart/cart-totals-{style}.php
```

### Cart Item Themes

The `cart_theme_style` setting (from `MODERNCART_SETTINGS`) determines which cart item template is used:

| Style | Template | Description |
|-------|----------|-------------|
| `style1` | `cart/cart-item-style1.php` | Default layout |
| `style2` | `cart/cart-item-style2.php` | Alternative layout |

The theme style is validated against the options defined in `Settings_Fields::get_fields()`. If the saved value is not in the available options, it falls back to `'style1'`.

### Header Styles

| Style | Template |
|-------|----------|
| `style1` | `cart/header-style1.php` |
| `style2` | `cart/header-style2.php` |

### Order Summary Styles

| Style | Template |
|-------|----------|
| `style1` | `cart/cart-totals-style1.php` |
| `style2` | `cart/cart-totals-style2.php` |

A `cart/cart-totals-simple.php` template is also available for minimal total display.

### Drawer Classes

The outer modal element receives the following CSS classes (filterable via `moderncart_modal_slide_out_classes`):

- `moderncart-plugin`
- `moderncart-modal`
- `moderncart-cart-style-slideout`
- `moderncart-recommendation-style-style1`
- `moderncart-cart-theme-{cart_theme_style}`
- `moderncart-slide-right`

The inner drawer receives classes (filterable via `moderncart_slide_out_classes`):

- `moderncart-default-slide-out`
- `moderncart-modal-wrap`
- `moderncart-animation-simple`
- `moderncart-{order_summary_style}-order-summary-style`
- `moderncart-image-size-{product_image_size}`

### AJAX Refresh

After any cart operation, the frontend JS replaces the contents of the drawer using the `shop/slide-out-inner.php` template (the drawer contents without the outer wrapper). This avoids re-rendering the entire DOM element.

---

## Floating Cart Button

**Class:** `ModernCart\Inc\Floating` (`inc/floating.php`)
**Template:** `templates/shop/floating.php`
**Inner template (for AJAX refresh):** `templates/shop/floating-inner.php`

### Rendering Conditions

`Floating::floating_cart()` fires on `wp_footer` and renders only if:
1. `is_global_enabled()` returns `true`
2. `display_floating_cart_icon` setting is `true`

### Cart Icon

The floating button displays an SVG icon selected by index from `Helper::get_cart_icons()`, which returns an array of 10 SVG strings (indices 0–9). The index is stored in `floating_cart_icon` under `MODERNCART_FLOATING_SETTINGS`.

### Item Count Badge

The badge shows `Helper::get_cart_count()`, which returns `WC()->cart->get_cart_contents_count()` filtered by `moderncart_filter_cart_count`. Returns `0` if the cart is empty.

### Positioning

Position is controlled by `floating_cart_position` (`'bottom-right'` or `'bottom-left'`). The corresponding CSS is injected dynamically:

- `bottom-right`: `left: auto; right: 20px; flex-direction: row-reverse`
- `bottom-left`: `left: 20px; right: auto`

### Hide When Empty

When `enable_floating_if_empty` is `false` (default) and cart count is 0, the CSS class `moderncart-floating-cart-empty` is added to the launcher element. This class can be styled to hide the button.

### Astra Integration

`Floating` disables conflicting Astra cart features:
- Filters `astra_get_option_woo-header-cart-click-action` to return `''` (disables Astra slide-in)
- Filters `astra_get_option_shop-add-to-cart-action` to revert `slide_in_cart` to default
- On `wp_loaded`, removes `Astra_Builder_Header::mobile_cart_flyout` from `astra_footer`
- Adds `modern-cart-for-wc-available` class to Astra menu cart via `astra_cart_in_menu_class`

---

## Free Shipping Progress Bar

**Rendered by:** `Cart::render_free_shipping_bar()` via `moderncart_slide_out_header_before` action
**Template:** `templates/cart/free-shipping-bar.php`

### Enabling

Set `enable_free_shipping_bar` to `true` in `MODERNCART_MAIN_SETTINGS`.

### Threshold Detection

`Cart::get_free_shipping_amount()` calculates the minimum cart total for free shipping:

1. Gets the customer's shipping packages from `WC()->cart->get_shipping_packages()`
2. Determines the applicable shipping zone via `wc_get_shipping_zone()`
3. For unknown customers (no destination set), falls back to Zone ID `1`
4. Iterates zone shipping methods looking for `free_shipping` method
5. Returns `min_amount` when `requires` is not `'coupon'`

Can be overridden with `moderncart_free_shipping_min_amount` filter.

### Progress Calculation

```
remaining = min_amount - cart_total (after discounts)
percent = 100 - (remaining / min_amount) * 100
```

### States

| State | Condition | CSS Class |
|-------|-----------|-----------|
| In progress | `cart_total < min_amount` | `moderncart-slide-out-free-shipping-bar-wrapper` |
| Success | `cart_total >= min_amount` | `moderncart-slide-out-free-shipping-bar-wrapper--success` |

Text for each state is configurable via `free_shipping_bar_text` and `free_shipping_success_text` settings. `{amount}` in the bar text is replaced with `wc_price($remaining)`.

---

## Coupon Form

**Rendered by:** `Slide_Out::render_coupon_form()` via `moderncart_slide_out_footer_content` action (priority 25)
**Template:** `templates/cart/coupon-form.php`

The coupon form is only rendered when:
- `enable_coupon_field` is not `'disabled'`
- Cart is not empty

### Display Modes

| `enable_coupon_field` value | Behaviour |
|-----------------------------|-----------|
| `'minimize'` | Form starts collapsed (hidden), toggle arrow shown |
| `'expand'` | Form starts open |
| `'disabled'` | Coupon form not rendered |

When a coupon AJAX request returns an error, the form is forced to `'expand'` state.

### Applied Coupon Removal

`Slide_Out::render_coupon_removal()` fires on `moderncart_slide_out_coupon_form_after` and renders a row for each applied coupon with:
- Coupon code display
- Discount amount
- Remove button (triggers `moderncart_remove_coupon` AJAX)

---

## Cart Totals

**Rendered by:** `Slide_Out::render_totals()` (priority 35)
**Templates:** `cart/cart-totals-style1.php`, `cart/cart-totals-style2.php`

### Line Items Shown

| Item | Method | Condition |
|------|--------|-----------|
| Subtotal | `get_subtotal_html()` | Cart not empty, `moderncart_enable_subtotal` filter is `true` |
| Discount | `get_discount_html()` | `discount_total > 0`, `moderncart_enable_discount` filter is `true` |
| Shipping | `get_shipping_html()` | Shipping method available, `moderncart_enable_shipping` filter is `true` |
| Tax | `get_tax_html()` | Tax total > 0, `moderncart_enable_tax` filter is `true` |
| Total | `get_total_html()` | Always shown, `moderncart_enable_total` filter is `true` |

### Total Display

The total shows:
- Bold current total: `<strong>wc_price($total)</strong>`
- Tax breakdown in `<small class="includes_tax">` when prices include tax
- Strikethrough pre-discount total in `<del>` when a discount is applied

### Checkout Button

When cart has items: uses `checkout_button_label` setting and links to `wc_get_checkout_url()` (filterable via `moderncart_checkout_button_url`).

When cart is empty: uses `empty_cart_button_text` setting and links to the shop page (filterable via `moderncart_empty_cart_button_url`).

---

## Product Recommendations

### In-Cart Recommendations (Non-empty Cart)

**Rendered by:** `Slide_Out` (via `moderncart_slide_out_cart_after` action when cart has items)
**Template:** `templates/cart/recommendations.php`

`Slide_Out::get_recommendations()` fetches products based on `recommendation_types`:

| Type | Source |
|------|--------|
| `'upsells'` | `WC_Product::get_upsell_ids()` for items in cart |
| `'cross_sells'` | `WC_Product::get_cross_sell_ids()` for items in cart |
| Fallback | `get_random_products()` — 8 random in-stock, published, non-hidden products |

### Empty Cart Recommendations

**Rendered by:** `Slide_Out::render_empty_cart_recommendations()` via `moderncart_slide_out_cart_after`
**Template:** `templates/cart/recommendation-empty.php`

Shown when `empty_cart_recommendation` is not `'disabled'` and cart is empty.

`get_empty_cart_recommendations()` fetches up to 5 products (filterable via `moderncart_empty_cart_recommendation_limit`):

| Type | Source |
|------|--------|
| `'featured'` | `WC_Product_Query` with `featured => true`, randomised |
| `'upsells'` | Products from `_upsell_ids` post meta, randomised |
| `'cross_sells'` | Products from `_crosssell_ids` post meta, randomised |

Results are statically cached per request to avoid duplicate DB queries.

---

## Custom Price Support

`Slide_Out::set_custom_prices()` hooks into `woocommerce_before_calculate_totals` (priority 10) and checks each cart item for a `custom_price` key. If found, it calls `$cart_item['data']->set_price($cart_item['custom_price'])`.

This enables Pro features (and third-party code) to inject custom pricing into cart items without modifying WooCommerce's core price handling.

---

## Express Checkout Integration

Modern Cart integrates with CartFlows Stripe (CPSW) express checkout:

- `Slide_Out::express_checkout_location_status()` returns `true` via `cpsw_express_checkout_selected_location_status` — enables express checkout in the cart drawer
- `Slide_Out::express_checkout_show_all_pages()` returns `true` via `cpsw_express_checkout_allow_custom_pages` — allows express checkout on all pages when Modern Cart is enabled
- `Slide_Out::action_request_button_before()` adds an "OR" separator before the express checkout button via `cpsw_payment_request_button_before`

The express checkout area is hidden via CSS when `enable_express_checkout` setting is `false`:

```css
.moderncart-slide-out-footer #cpsw-payment-request-wrapper {
    display: none !important;
}
```

---

## Quantity Selectors

**Rendered by:** `Slide_Out::render_quantity_selectors()`
**Template:** `templates/cart/quantity-selector.php`

Not rendered for products where `$product->is_sold_individually()` is `true`.

The selector respects:
- `get_max_purchase_quantity()` — sets `max` attribute
- `get_min_purchase_quantity()` — sets `min` attribute (minimum of 0 enforced)
- `has_filter('woocommerce_stock_amount', 'intval')` — sets `pattern="[0-9]*"` and `inputmode="numeric"`

All selector attributes are filterable via `moderncart_woocommerce_quantity_input_args`.

---

## Product Name & Details

`Slide_Out::get_product_name()` builds the cart item name HTML:

1. Product name as a link if `$product->is_visible()`, otherwise plain text
2. Filtered via `moderncart_woocommerce_cart_item_name`
3. "View details" collapsible toggle if `wc_get_formatted_cart_item_data()` returns data (filterable via `moderncart_after_cart_item_name_hook_collapsible`)
4. Backorder notification if product is on backorder

---

## Maintenance Mode Guard

`Floating::__construct()` checks `Helper::is_maintenance_mode()` before registering the `wp_footer` hook for the floating button. If the site is in maintenance mode, the floating cart is not rendered.

Maintenance mode is detected from:
- WooCommerce "Coming Soon" mode (with private link key check)
- Elementor maintenance mode
- WordPress core `wp_is_maintenance_mode()`

Admins (`manage_options` capability) bypass the maintenance check.
