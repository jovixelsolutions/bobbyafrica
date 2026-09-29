# Settings Reference

All Modern Cart settings are stored in `wp_options` across four option keys. The `Helper::get_defaults()` method is the single source of truth for default values and data types.

---

## Option Groups Overview

| Constant | `wp_options` key | Description |
|----------|-----------------|-------------|
| `MODERNCART_MAIN_SETTINGS` | `moderncart_setting` | Global enable/disable and feature toggles |
| `MODERNCART_SETTINGS` | `moderncart_cart` | Cart appearance, behaviour, and copy |
| `MODERNCART_FLOATING_SETTINGS` | `moderncart_floating` | Floating cart button options |
| `MODERNCART_APPEARANCE_SETTINGS` | `moderncart_appearance` | Colour and typography |

---

## Reading Settings in PHP

Always use `Helper::get_option()` — it merges DB values with defaults and handles Astra theme colour inheritance:

```php
$helper = \ModernCart\Inc\Helper::get_instance();

// Get all settings for a group
$cart_settings = $helper->get_option( MODERNCART_SETTINGS );

// Read a single value from Cart base class
$title = $this->get_option( 'main_title', MODERNCART_SETTINGS, 'Default Title' );
```

---

## `moderncart_setting` — General Settings

| Key | Default | Type | Description |
|-----|---------|------|-------------|
| `enable_moderncart` | `'all'` | string | Scope: `'all'` (everywhere except checkout), `'disabled'` |
| `enable_powered_by` | `true` | boolean | Show "Powered by Modern Cart" in the drawer footer |
| `enable_ajax_add_to_cart` | `true` | boolean | Intercept standard add-to-cart buttons for AJAX behaviour |
| `enable_free_shipping_bar` | `false` | boolean | Show free shipping progress bar in the cart header |
| `enable_express_checkout` | `false` | boolean | Show CartFlows express checkout buttons in the cart footer |

**Notes:**
- When `enable_moderncart` is `'disabled'`, the cart drawer and floating button are not rendered at all
- When `'all'`, the cart is enabled on all pages *except* the WooCommerce checkout page (`is_checkout()` returns `false`)
- The cart is also enabled when `is_shop()`, `is_product()`, or `is_cart()` is `true`

---

## `moderncart_cart` — Cart Settings

### Layout & Style

| Key | Default | Type | Description |
|-----|---------|------|-------------|
| `cart_style` | `'slideout'` | string | Cart display style. Currently `'slideout'`. |
| `cart_theme_style` | `'style1'` | string | Cart item theme template (`'style1'`, `'style2'`, etc.) |
| `product_image_size` | `'medium'` | string | WooCommerce image size for cart item thumbnails |
| `cart_item_padding` | `20` | number | Horizontal padding (px) applied to each cart item row |
| `animation_speed` | `300` | number | CSS transition duration (ms) for drawer open/close |
| `section_styling` | `'accordian'` | string | Layout style for collapsible sections |
| `order_summary_style` | *(from fields)* | string | Order summary style: `'style1'` or `'style2'` |
| `cart_header_style` | *(from fields)* | string | Cart header style template: `'style1'` or `'style2'` |

### Copy / Labels

| Key | Default | Type | Description |
|-----|---------|------|-------------|
| `main_title` | `'Review Your Cart'` | string | Drawer header title when cart has items |
| `recommendation_title` | `'Even better with these!'` | string | Heading above product recommendations |
| `empty_cart_recommendation_title` | `'Let\'s find you something perfect'` | string | Heading above empty-cart recommendations |
| `coupon_title` | `'Got a Discount Code?'` | string | Coupon section heading |
| `coupon_placeholder` | `'Enter discount code'` | string | Coupon input placeholder text |
| `checkout_button_label` | `'Proceed to Checkout'` | string | CTA button label when cart has items |
| `empty_cart_button_text` | `'Your cart is empty. Shop now'` | string | CTA button label when cart is empty |
| `free_shipping_bar_text` | `'You\'re {amount} away from free shipping!'` | string | Progress bar text. `{amount}` is replaced with price. |
| `free_shipping_success_text` | `'Awesome pick! You\'ve unlocked free shipping.'` | string | Bar text shown when free shipping is unlocked |
| `on_sale_percentage_text` | `'You saved {percent}%'` | string | Sale badge on cart items. `{percent}` is replaced. |

### Coupon

| Key | Default | Type | Description |
|-----|---------|------|-------------|
| `enable_coupon_field` | `'minimize'` | string | Coupon display: `'minimize'` (collapsed), `'expand'` (open), `'disabled'` |

### Recommendations

| Key | Default | Type | Description |
|-----|---------|------|-------------|
| `recommendation_types` | *(from fields)* | string | Recommendation type when cart has items: `'upsells'`, `'cross_sells'`, or `'random_products'` |
| `empty_cart_recommendation` | `'disabled'` | string | What to show in empty cart: `'disabled'`, `'upsells'`, `'cross_sells'`, `'featured'` |

### Dimensions (Pro-extended)

| Key | Default | Type | Description |
|-----|---------|------|-------------|
| `slide_out_width_desktop` | `450` | number | Desktop drawer width (px) |
| `slide_out_width_mobile` | `80` | number | Mobile drawer width (%) |

---

## `moderncart_floating` — Floating Cart Settings

| Key | Default | Type | Description |
|-----|---------|------|-------------|
| `floating_cart_position` | `'bottom-right'` | string | Screen position: `'bottom-right'`, `'bottom-left'`, `'disabled'` |
| `floating_cart_icon` | `0` | number | Index into the SVG icon array returned by `Helper::get_cart_icons()` (0–9) |
| `display_floating_cart_icon` | `true` | boolean | Whether to show the floating button at all |
| `enable_floating_if_empty` | `false` | boolean | When `true`, adds `moderncart-floating-cart-empty` class to hide button when cart is empty |
| `custom_trigger_selectors` | *(empty)* | string | CSS selectors for custom elements that should open the drawer |

---

## `moderncart_appearance` — Appearance Settings

| Key | Default | Type | Description |
|-----|---------|------|-------------|
| `primary_color` | `'#0284C7'` | hex | Primary accent colour (buttons, icons, progress bar) |
| `heading_color` | `'#1F2937'` | hex | Cart header and section heading text colour |
| `body_color` | `'#374151'` | hex | Body text colour for cart items and labels |
| `cart_header_text_alignment` | `'center'` | string | Header text alignment: `'left'`, `'center'`, `'right'` |
| `cart_header_font_size` | `22` | number | Header font size (px) |
| `highlight_color` | `'#10B981'` | hex | Highlight/success colour |
| `background_color` | `'#FFFFFF'` | hex | Cart drawer background |
| `button_font_color` | `'#FFFFFF'` | hex | Text colour on primary buttons |
| `header_font_color` | *(heading_color)* | hex | Cart header text colour |
| `header_background_color` | `'#FFFFFF'` | hex | Cart header background colour |
| `quantity_font_color` | `'#1F2937'` | hex | Quantity selector text colour |
| `quantity_background_color` | `'#EAEFF3'` | hex | Quantity selector background |
| `icon_color` | `'#FFFFFF'` | hex | Floating button icon colour |
| `count_text_color` | `'#FFFFFF'` | hex | Item count badge text colour |
| `count_background_color` | `'#10B981'` | hex | Item count badge background |
| `icon_background_color` | `primary_color` | hex | Floating button background colour |

**Astra Theme:** When Astra is active, appearance settings fall back to Astra's global CSS variable palette (`--ast-global-color-0` through `--ast-global-color-6`) on the frontend. In the admin, actual hex values from Astra's `global-color-palette` option are used.

---

## CSS Custom Properties

All appearance values are output as CSS custom properties on `:root` by `Scripts::dynamic_styles()`:

| CSS Variable | Source |
|-------------|--------|
| `--moderncart-primary-color` | `primary_color` |
| `--moderncart-heading-color` | `heading_color` |
| `--moderncart-body-color` | `body_color` |
| `--moderncart-background-color` | `#FFFFFF` (hardcoded default) |
| `--moderncart-highlight-color` | `#10B981` (hardcoded default) |
| `--moderncart-button-font-color` | `#FFFFFF` (hardcoded default) |
| `--moderncart-floating-icon-bg-color` | `primary_color` |
| `--moderncart-floating-icon-color` | `#FFFFFF` (hardcoded default) |
| `--moderncart-floating-count-text-color` | `#FFFFFF` (hardcoded default) |
| `--moderncart-floating-count-bg-color` | `#10B981` (hardcoded default) |
| `--moderncart-cart-header-text-alignment` | `cart_header_text_alignment` |
| `--moderncart-cart-header-font-size` | `cart_header_font_size` + `px` |
| `--moderncart-slide-out-desktop-width` | `slide_out_width_desktop` + `px` |
| `--moderncart-slide-out-mobile-width` | `slide_out_width_mobile` + `%` |
| `--moderncart-animation-duration` | `animation_speed` + `ms` |
| `--moderncart-cart-item-padding` | `cart_item_padding` + `px` |

For any variable containing `-color` with a valid 6-digit hex value, a `{var}-light` variant is also generated with 12% opacity (by appending `12` to the hex).

---

## Sanitization

Settings are sanitized in `Admin_Menu::sanitize_data()` based on the schema `type`:

| Type | Sanitizer |
|------|-----------|
| `'boolean'` | `rest_sanitize_boolean()` |
| `'number'` | `absint()` |
| `'hex'` | `sanitize_hex_color()` |
| `'string'` (default) | `sanitize_text_field()` |

Only keys present in the schema are saved — unknown keys are silently dropped.
