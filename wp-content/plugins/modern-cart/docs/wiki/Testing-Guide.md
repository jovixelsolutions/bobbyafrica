# Testing Guide

Modern Cart uses PHPUnit for PHP unit and integration tests. JavaScript linting and formatting are enforced via ESLint and Prettier.

---

## PHP Testing

### Setup

```bash
cd app/public/wp-content/plugins/modern-cart/

# Install PHP dev dependencies (phpunit, phpcs, phpstan, etc.)
composer install
```

PHPUnit configuration is defined in `phpunit.xml` (or `phpunit.xml.dist`) in the plugin root.
PHPStan configuration: `phpstan.neon`
PHPStan baseline (known suppressions): `phpstan-baseline.neon`

### Running Tests

```bash
# Run PHPUnit tests
composer test

# Run PHPStan static analysis
composer phpstan

# Run PHPCS code standards check
composer lint

# Auto-fix PHPCS violations
composer format

# Run PHPInsights analysis
composer insights
```

### Test Location

```
tests/php/          # PHPUnit test files
tests/php/stubs/    # PHPStan stubs for type checking
```

---

## PHP Code Standards

The plugin uses the following PHPCS rulesets (defined in `phpcs.xml`):

| Ruleset | Purpose |
|---------|---------|
| WordPress-Core | WordPress core coding standards |
| WordPress-Docs | Documentation standards (docblocks) |
| WordPress-Extra | Additional WordPress best practices |
| VIP-Go | WordPress VIP hosting standards (stricter) |
| PHPCompatibility | PHP version compatibility checks |

### Common PHPCS Patterns

**Nonce verification before form/AJAX data:**
```php
if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'action_name' ) ) {
    wp_die();
}
```

**Input sanitization:**
```php
$value = sanitize_text_field( wp_unslash( $_POST['field'] ) );
$id    = absint( $_POST['id'] );
$hex   = sanitize_hex_color( $_POST['color'] );
```

**Output escaping:**
```php
echo esc_html( $string );
echo esc_attr( $attr );
echo esc_url( $url );
echo wp_kses_post( $html );
```

**Database queries (rare — WooCommerce APIs preferred):**
```php
$results = $wpdb->get_col(
    $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s LIMIT %d", $key, $limit )
);
```

---

## PHPStan

PHPStan runs at a strict level with WordPress stubs provided by `szepeviktor/phpstan-wordpress` and WooCommerce stubs from `php-stubs/woocommerce-stubs`.

```bash
# Generate stubs from current plugin build (Pro plugin only)
composer gen-stubs

# Update stubs (runs build + gen-stubs)
composer update-stubs

# Regenerate PHPStan baseline
composer gen-baseline
```

`phpstan-baseline.neon` contains known suppressions — do not add suppressions for new code; fix the issues instead.

---

## JavaScript Linting

```bash
# Check JS code standards (ESLint via wp-scripts)
npm run lint-js

# Fix JS code standards automatically
npm run lint-js:fix   # Runs prettier + eslint --fix

# Check CSS/PostCSS standards
npm run lint-css

# Fix CSS issues
npm run lint-css:fix

# Check Prettier formatting
npm run pretty

# Auto-fix Prettier formatting
npm run pretty:fix
```

ESLint configuration follows `@wordpress/eslint-plugin` rules (enforced by `@wordpress/scripts`).
Prettier configuration follows `@wordpress/prettier-config`.

---

## Build Verification

Before committing changes, run the full quality pipeline:

```bash
# PHP
composer format   # Fix PHPCS
composer lint     # Verify PHPCS
composer phpstan  # Static analysis

# JavaScript
npm run lint-js:fix    # Fix + lint JS
npm run lint-css:fix   # Fix + lint CSS
npm run build          # Verify production build succeeds
```

---

## Writing a PHPUnit Test

Tests live in `tests/php/`. Follow these patterns:

```php
<?php
namespace ModernCart\Tests;

use PHPUnit\Framework\TestCase;
use ModernCart\Inc\Helper;

class HelperTest extends TestCase {

    public function test_convert_to_int_with_numeric_string(): void {
        $this->assertSame( 42, Helper::convert_to_int( '42' ) );
    }

    public function test_convert_to_int_with_non_numeric(): void {
        $this->assertSame( 0, Helper::convert_to_int( 'hello' ) );
    }

    public function test_convert_to_array_with_null(): void {
        $this->assertSame( [], Helper::convert_to_array( null ) );
    }
}
```

### Helper Utility Methods to Test

| Method | Class | Returns |
|--------|-------|---------|
| `convert_to_string($data)` | `Helper` | string |
| `convert_to_int($value, $base)` | `Helper` | int |
| `convert_to_array($data)` | `Helper` | array |
| `is_cart_empty()` | `Helper` | bool |
| `is_astra_active()` | `Helper` | bool |
| `get_cart_count()` | `Helper` | int |
| `get_pro_status()` | `Helper` | string |
| `is_admin_onboarding_screen()` | `Helper` | bool |
| `is_maintenance_mode()` | `Helper` | bool |
| `is_nav_menu_widget_render_request()` | `Helper` | bool |

---

## Manual Test Checklist

When developing, verify these behaviours manually:

### Cart Drawer

- [ ] Drawer opens when floating button is clicked
- [ ] Drawer closes when overlay or close button is clicked
- [ ] Animation speed matches `animation_speed` setting
- [ ] Drawer does **not** render on checkout page (`is_checkout()`)
- [ ] Drawer renders on shop, product, and cart pages

### Add to Cart

- [ ] Clicking "Add to Cart" on shop page opens drawer (when AJAX enabled)
- [ ] Toast notification appears on success
- [ ] Drawer refreshes with new product added
- [ ] Sold-individually product: error if already in cart

### Cart Operations

- [ ] Quantity increase/decrease updates cart via AJAX
- [ ] Quantity set to 0 removes item, shows "Undo?" link
- [ ] Undo restores removed item
- [ ] Remove button removes item

### Coupon

- [ ] Valid coupon applied shows success notice
- [ ] Invalid coupon shows error and expands coupon field
- [ ] Already-applied coupon shows appropriate error
- [ ] Coupon tag shows with remove button
- [ ] Removing coupon recalculates totals

### Free Shipping Bar

- [ ] Progress bar visible when `enable_free_shipping_bar` is `true`
- [ ] Bar shows remaining amount with formatted price
- [ ] Bar shows success state when threshold met
- [ ] Bar hidden when no free shipping method in zone

### Floating Button

- [ ] Button appears at configured position (bottom-right/bottom-left)
- [ ] Count badge reflects cart item count
- [ ] Badge hides/shows when cart empties/fills
- [ ] Button hidden during WooCommerce "Coming Soon" mode

### Astra Compatibility (if testing with Astra)

- [ ] Astra's header cart icon opens Modern Cart drawer (not Astra's drawer)
- [ ] Astra's "slide_in_cart" shop action is disabled
- [ ] Astra's mobile cart flyout is not shown
- [ ] `modern-cart-for-wc-available` class added to Astra cart menu item
- [ ] Colours match Astra's global colour palette

---

## Generating i18n Test Files

```bash
# Generate .pot file
npm run i18n

# Update .po files from .pot
npm run i18n:po

# Compile .mo binaries from .po files
npm run i18n:mo

# Generate JSON translation files for JS
npm run i18n:json
```

For AI-assisted translation (requires `gpt-po` package):

```bash
npm run i18n:gptpo:nl   # Dutch
npm run i18n:gptpo:fr   # French
npm run i18n:gptpo:de   # German
npm run i18n:gptpo:es   # Spanish
npm run i18n:gptpo:it   # Italian
npm run i18n:gptpo:pt   # Portuguese
npm run i18n:gptpo:pl   # Polish
```
