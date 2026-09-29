# Theme Compatibility

Modern Cart is designed to work with any WooCommerce-compatible theme. This page documents theme-specific integrations, with a focus on the deep Astra integration built into the plugin.

---

## Supported Themes

The plugin has been explicitly tested and confirmed compatible with:

| Theme | Notes |
|-------|-------|
| Astra | Deep integration — see below |
| Spectra One | Compatible |
| Blocksy | Compatible |
| Kadence | Compatible |
| GeneratePress | Compatible |
| OceanWP | Compatible |
| Hello Elementor | Compatible |
| Storefront | Compatible |
| Bricks Builder | Compatible |
| Flatsome | Compatible |
| Hestia | Compatible |
| Neve | Compatible |

For any theme, Modern Cart renders its own drawer and floating button via `wp_footer`. No theme modification is required.

---

## Astra Theme Integration

Astra is the primary theme with built-in Modern Cart support. The integration handles two problems:
1. **Conflict**: Astra has its own slide-in cart and mobile cart flyout that duplicate Modern Cart's functionality
2. **Colors**: Astra has a global colour palette that Modern Cart should respect

### Detecting Astra

```php
public static function is_astra_active(): bool {
    return defined( 'ASTRA_THEME_VERSION' );
}
```

`ASTRA_THEME_VERSION` is defined in the Astra theme.

---

### Disabling Astra's Conflicting Cart

`Floating::__construct()` registers three hooks to neutralise Astra's built-in cart:

#### 1. Disable Astra Slide-In Cart Action (Header)

```php
add_filter( 'astra_get_option_woo-header-cart-click-action', [ $this, 'modify_astra_slideout' ] );
// Returns ''  — removes the slide-in action from Astra's header cart icon
```

#### 2. Disable Astra Slide-In Cart on Shop Pages

```php
add_filter( 'astra_get_option_shop-add-to-cart-action', [ $this, 'disable_astra_slideout' ], 10, 3 );
```

If `$value === 'slide_in_cart'` (Astra's slide-in setting), it's reset to the default value, preventing Astra from intercepting "Add to Cart" clicks.

#### 3. Remove Astra Mobile Cart Flyout

```php
add_action( 'wp_loaded', [ $this, 'disable_astra_mobile_slideout' ] );
```

Removes `Astra_Builder_Header::mobile_cart_flyout` from the `astra_footer` action so Astra's mobile slide-out doesn't appear alongside Modern Cart's drawer.

#### 4. Astra Menu Cart Class

```php
add_filter( 'astra_cart_in_menu_class', [ $this, 'modify_mini_cart_classes' ] );
// Adds 'modern-cart-for-wc-available' to the Astra header cart icon element
```

This CSS class signals to Astra's JavaScript that Modern Cart is handling cart interactions, preventing Astra from also handling clicks.

---

### Astra Colour Integration

When Astra is active, Modern Cart inherits Astra's global colour palette for consistent visual design.

#### `Helper::get_astra_color_vars()`

Returns an array of colour values that map Modern Cart's settings to Astra's palette:

**On the frontend** (uses CSS variables — no PHP colour values needed):

```php
return [
    'primary_color'             => 'var(--ast-global-color-0)',
    'heading_color'             => 'var(--ast-global-color-1)',
    'body_color'                => 'var(--ast-global-color-2)',
    'highlight_color'           => 'var(--ast-global-color-3)',
    'background_color'          => 'var(--ast-global-color-4)',
    'button_font_color'         => 'var(--ast-global-color-5)',
    'header_font_color'         => 'var(--ast-global-color-1)',
    'header_background_color'   => 'var(--ast-global-color-4)',
    'quantity_font_color'       => 'var(--ast-global-color-1)',
    'quantity_background_color' => 'var(--ast-global-color-6)',
    'icon_color'                => 'var(--ast-global-color-5)',
    'count_text_color'          => 'var(--ast-global-color-1)',
    'count_background_color'    => 'var(--ast-global-color-6)',
    'icon_background_color'     => 'var(--ast-global-color-0)',
];
```

**In the admin** (uses actual hex values from Astra's palette setting):

```php
$theme_colors = astra_get_option( 'global-color-palette' );
$color_preset = $theme_colors['palette']; // Array of 7+ hex strings
return [
    'primary_color'   => $color_preset[0],
    'heading_color'   => $color_preset[1],
    'body_color'      => $color_preset[2],
    // ...
];
```

#### Default Colour Preset (Fallback)

When Astra is not active or has no custom palette:

```php
$color_preset = ['#046bd2', '#045cb4', '#1e293b', '#334155', '#F0F5FA', '#FFFFFF', '#D1D5DB', '#111111'];
```

#### Colour Inheritance in Settings

`Helper::get_option()` applies Astra colours automatically:

```php
if ( ( MODERNCART_APPEARANCE_SETTINGS === $option || MODERNCART_FLOATING_SETTINGS === $option )
     && 'astra' === get_template() ) {
    $default = array_merge( $default, self::get_astra_color_vars() );
}
```

When `get_template()` returns `'astra'`, appearance and floating settings default to Astra's CSS variable palette rather than Modern Cart's hardcoded defaults.

---

## CSS Custom Properties on `:root`

All colour and dimension settings are output as CSS custom properties by `Scripts::dynamic_styles()`. This means themes and child themes can override them:

```css
/* In your theme's style.css or a child theme */
:root {
    --moderncart-primary-color: #your-brand-color !important;
    --moderncart-background-color: #f9f9f9 !important;
}
```

See [Settings-Reference](Settings-Reference) for the full list of CSS variables.

---

## RTL Support

Modern Cart handles right-to-left languages:

### CSS RTL Files

Both frontend and admin CSS have RTL variants:
```php
wp_style_add_data( 'moderncart-cart-css', 'rtl', 'replace' );
wp_style_add_data( 'moderncart_settings', 'rtl', 'replace' );
```

WordPress automatically loads `cart-rtl.css` and `settings-rtl.css` when `is_rtl()` is `true`.

### RTL-Specific Inline CSS

`Scripts::dynamic_styles()` adds a special rule when `is_rtl()` is `true`:

```css
#moderncart-slide-out .moderncart-slide-out-footer
  .moderncart-order-summary-style-style2
  #moderncart-coupon-form-container
  .moderncart-coupon-remove {
    justify-content: right;
}
```

### Coupon Pricing RTL

In `moderncart_cart_item_price()`, the sale percentage text is adjusted for RTL:

```php
$savings_text = is_rtl()
    ? "%{$savings_text}"   // % before text for RTL
    : "{$savings_text}%";  // % after text for LTR
```

---

## WooCommerce HPOS Compatibility

Modern Cart declares compatibility with WooCommerce High Performance Order Storage (HPOS/Custom Order Tables):

```php
\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
    'custom_order_tables',
    MODERNCART_FILE,
    true
);
```

This prevents the WooCommerce HPOS incompatibility notice from appearing in the store admin.

---

## Maintenance Mode Compatibility

`Helper::is_maintenance_mode()` checks for maintenance mode from multiple plugins:

| Plugin/System | Check Method |
|---------------|-------------|
| WooCommerce Coming Soon | `ComingSoonHelper::is_site_coming_soon()` / `is_store_coming_soon()` |
| WooCommerce Private Link | `woocommerce_private_link` option + `woo-share` cookie |
| Elementor Maintenance Mode | `Elementor\Maintenance_Mode::get('mode') === 'maintenance'` |
| WordPress Core | `wp_is_maintenance_mode()` |

Admins (users with `manage_options`) bypass all maintenance mode checks.

---

## Nav Menu Widget Compatibility

`Helper::is_nav_menu_widget_render_request()` detects REST API requests for nav menu widgets and returns `false` from `is_global_enabled()` to prevent the cart drawer from being output during block widget rendering:

```php
if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
    $request_uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
    return strpos( $request_uri, '/wp-json/wp/v2/widget-types/' ) !== false;
}
```

---

## Known Compatible Plugins

| Plugin | Compatibility Notes |
|--------|---------------------|
| CartFlows | Deep integration — express checkout, edit cart button |
| Cart Abandonment Recovery | Compatible |
| Variation Swatches for WooCommerce | Compatible |
| OttoKit | Used for onboarding webhook |
| Spectra / Elementor / Beaver Builder | Page builder compatibility (no conflicts) |
| TutorLMS / LifterLMS / LearnDash / MemberPress | Compatible |
| WooCommerce Subscriptions | Compatible |
| TranslatePress | Fix in v1.0.6 — handles unwanted HTML in product names |
