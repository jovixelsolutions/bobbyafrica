# Template System

Modern Cart uses a WordPress-style template system with theme override support. All templates are loaded through the `moderncart_get_template_part()` global function defined in `inc/functions.php`.

---

## Template Helper Function

```php
moderncart_get_template_part( $slug, $name = '', $args = [], $return = false )
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$slug` | string | Template slug (e.g., `'cart/cart-item'`) |
| `$name` | string | Optional sub-name (e.g., `'style1'`) — appended as `{slug}-{name}.php` |
| `$args` | array | Variables to extract into template scope |
| `$return` | bool | When `true`, returns HTML string (using `ob_start`); when `false`, echoes directly |

### Resolution Order

1. Look in theme: `{theme}/modern-cart/{slug}-{name}.php` → `{theme}/modern-cart/{slug}.php`
2. Apply `moderncart_set_template_path` filter on the base template directory
3. Look in plugin: `{MODERNCART_PLUGIN_PATH}/templates/{slug}-{name}.php`
4. Apply `moderncart_get_template_part` filter on the resolved path
5. If still not found, return empty string

### Theme Override

To override any template, create a file in your theme at:

```
{your-theme}/modern-cart/{slug}-{name}.php
```

**Examples:**

| Plugin template | Theme override path |
|-----------------|---------------------|
| `templates/cart/cart-item-style1.php` | `{theme}/modern-cart/cart/cart-item-style1.php` |
| `templates/shop/floating.php` | `{theme}/modern-cart/shop/floating.php` |
| `templates/cart/header-style1.php` | `{theme}/modern-cart/cart/header-style1.php` |

---

## All Template Files

### Shop Templates (`templates/shop/`)

| File | Called By | Description |
|------|-----------|-------------|
| `shop/slide-out.php` | `Slide_Out::slide_out()` | Outer slide-out modal wrapper. Fires `moderncart_slide_out_content` action inside. |
| `shop/slide-out-inner.php` | All Slide_Out AJAX handlers | Inner drawer content only (refreshed via AJAX without re-creating the wrapper). |
| `shop/floating.php` | `Floating::floating_cart()` | Full floating cart button (wrapper + inner). |
| `shop/floating-inner.php` | `Floating_Ajax::refresh_floating_cart()` | Floating button inner content (icon + count badge, refreshed via AJAX). |

### Cart Header Templates (`templates/cart/`)

| File | Called By | Description |
|------|-----------|-------------|
| `cart/header-style1.php` | `Slide_Out::render_header()` | Default header — title on left, close button on right. |
| `cart/header-style2.php` | `Slide_Out::render_header()` | Alternative header layout. |

**Available `$data` in header templates:**

| Variable | Type | Description |
|----------|------|-------------|
| `$classes` | array | CSS classes for header element |
| `$title` | string | Cart drawer title text |
| `$quantity` | int | Number of items in cart |

### Cart Item Templates

| File | Called By | Description |
|------|-----------|-------------|
| `cart/cart-item-style1.php` | `Slide_Out::render_contents()` | Default cart item row layout |
| `cart/cart-item-style2.php` | `Slide_Out::render_contents()` | Alternative cart item row layout |

**Available `$data` in cart item templates:**

| Variable | Type | Description |
|----------|------|-------------|
| `$product_name` | string | Product name HTML (with link, variation details) |
| `$classes` | array | CSS classes for the item row |
| `$quantity` | string | Rendered quantity selector HTML |
| `$delete` | bool | Whether to show a delete button |
| `$cart_item` | array | Full WooCommerce cart item array |
| `$cart_item_key` | string | WooCommerce cart item key |
| `$product` | WC_Product | The product object |
| `$product_id` | int | Product ID |
| `$product_permalink` | string | Product URL (empty if not visible) |
| `$thumbnail` | string | Product thumbnail `<img>` HTML |
| `$product_subtotal` | string | Price HTML including sale percentage |

### Cart Totals Templates

| File | Called By | Description |
|------|-----------|-------------|
| `cart/cart-totals-style1.php` | `Slide_Out::render_totals()` | Standard totals layout with stacked line items |
| `cart/cart-totals-style2.php` | `Slide_Out::render_totals()` | Alternative totals layout |
| `cart/cart-totals-simple.php` | (available for custom use) | Minimal totals display |

**Available `$data` in totals templates:**

| Variable | Type | Description |
|----------|------|-------------|
| `$order_summary_style` | string | `'style1'` or `'style2'` |
| `$classes` | array | CSS classes |
| `$subtotal` | string | Subtotal line item HTML |
| `$discount` | string | Discount line item HTML |
| `$shipping` | string | Shipping line item HTML |
| `$total` | string | Total line item HTML |
| `$tax` | string | Tax line item HTML |
| `$button_text` | string | CTA button label |
| `$url` | string | CTA button href |
| `$coupon` | array | `{title, placeholder_text}` for inline coupon |
| `$data_args` | array | Args passed from footer action |

### Cart Utility Templates

| File | Called By | Description |
|------|-----------|-------------|
| `cart/footer.php` | `Slide_Out::render_footer()` | Footer wrapper. Fires `moderncart_slide_out_footer_content` action. |
| `cart/coupon-form.php` | `Slide_Out::render_coupon_form()` | Coupon code input form. Fires `moderncart_slide_out_coupon_form_after` after form. |
| `cart/empty-state.php` | `Slide_Out::render_contents()` | Empty cart message display. |
| `cart/free-shipping-bar.php` | `Cart::render_free_shipping_bar()` | Progress bar toward free shipping threshold. |
| `cart/powered-by.php` | `Cart::render_poweredby()` | "Powered by Modern Cart" footer link. |
| `cart/price.php` | Cart item rendering | Individual price display. |
| `cart/quantity-selector.php` | `Slide_Out::render_quantity_selectors()` | +/- quantity control. |
| `cart/recommendation-empty.php` | `Slide_Out::render_empty_cart_recommendations()` | Product grid for empty cart state. |
| `cart/recommendations.php` | `Slide_Out` (in-cart recommendations) | Product recommendation grid. |

### Free Shipping Bar Template Data

| Variable | Type | Description |
|----------|------|-------------|
| `$classes` | string | Space-separated CSS classes |
| `$content` | string | Bar text with `{amount}` already replaced |
| `$percent` | float | Fill percentage (0–100) |

### Coupon Form Template Data

| Variable | Type | Description |
|----------|------|-------------|
| `$classes` | array | CSS classes |
| `$active_coupon` | array | Array of applied coupon codes |
| `$currency_symbol` | string | WooCommerce currency symbol |
| `$moderncart_coupon` | string | Currently applied coupon code |
| `$title` | string | Coupon section heading |
| `$placeholder_text` | string | Input placeholder |
| `$button_text` | string | "Apply" button label |
| `$arrow_down` | string | Empty string or `'moderncart-hide'` |
| `$arrow_up` | string | `'moderncart-hide'` or empty string |
| `$data_args` | array | Args from footer action (for error state) |

---

## Data Attributes Used by JS

The frontend JS (`cart.js`) relies on these HTML data attributes:

| Attribute | Element | Purpose |
|-----------|---------|---------|
| `data-moderncart-toggle="collapse"` | `<span>` | Toggles collapsible section |
| `data-moderncart-target="moderncart-collapse-{key}"` | `<span>` | ID of element to toggle |
| `data-key="{cart_item_key}"` | Remove/restore buttons | Identifies cart item for AJAX |
| `data-action="increase"/"decrease"` | Quantity buttons | Quantity change direction |
| `data-coupon="{code}"` | Coupon remove button | Coupon code to remove |
| `data-type="{success/error}"` | Notification `<div>` | Notification styling type |

---

## Quantity Selector Template Data

| Variable | Type | Description |
|----------|------|-------------|
| `$input_id` | string | Unique ID for input element |
| `$input_name` | string | Input name attribute |
| `$input_value` | int | Current quantity |
| `$classes` | array | CSS classes for input |
| `$max_value` | int/string | Max quantity (`''` = unlimited) |
| `$min_value` | int | Min quantity (≥ 0) |
| `$step` | int | Increment step |
| `$pattern` | string | HTML pattern attribute |
| `$inputmode` | string | `'numeric'` or `''` |
| `$product_name` | string | For ARIA label |
| `$placeholder` | string | Input placeholder |
| `$cart_item_key` | string | Cart item key for AJAX |

---

## Adding Custom Templates (Developers)

### Method 1: Theme Override

Copy the template from the plugin to your theme and modify it:

```bash
cp wp-content/plugins/modern-cart/templates/cart/cart-item-style1.php \
   wp-content/themes/your-theme/modern-cart/cart/cart-item-style1.php
```

### Method 2: Filter the Template Path

Override the base template directory to serve templates from a different plugin:

```php
add_filter( 'moderncart_set_template_path', function( $path, $template_args ) {
    // Serve from your plugin's templates dir
    return MY_PLUGIN_DIR . 'templates';
}, 10, 2 );
```

### Method 3: Replace a Specific Template

```php
add_filter( 'moderncart_get_template_part', function( $template, $slug, $name ) {
    if ( 'cart/cart-item' === $slug && 'style1' === $name ) {
        return MY_PLUGIN_DIR . 'templates/custom-cart-item.php';
    }
    return $template;
}, 10, 3 );
```

### Method 4: Add Content via Actions

```php
// Add content inside the cart footer
add_action( 'moderncart_slide_out_footer_content', function( $args ) {
    echo '<div class="my-custom-section">Custom content</div>';
}, 20 ); // Between coupon form (25) and totals (35) — or adjust priority
```
