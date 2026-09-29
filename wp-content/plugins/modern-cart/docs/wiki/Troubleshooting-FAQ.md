# Troubleshooting & FAQ

Common issues and their solutions, derived from the plugin changelog and known compatibility requirements.

---

## Cart Not Appearing

### Cart Shows on Some Pages But Not Others

**Expected behaviour:** The cart drawer and floating button are **intentionally hidden** on the WooCommerce checkout page to prevent UX conflicts.

The cart is enabled when `enable_moderncart` is `'all'` AND:
- The page is NOT `is_checkout()`
- OR the page is `is_shop()`, `is_product()`, or `is_cart()`

**If the cart is missing on other pages**, check:
1. Navigate to **WooCommerce → Modern Cart → General tab**
2. Verify `enable_moderncart` is not set to `'disabled'`
3. Check if any plugin is returning `false` via the `moderncart_override_is_global_enabled` filter

### Cart Not Visible — Maintenance Mode Active

When the site is in maintenance mode (WooCommerce Coming Soon, Elementor maintenance, or WordPress core maintenance), the floating cart button is not rendered. This is intentional.

Admins (`manage_options` capability) can still see the cart.

**Fix:** Disable maintenance mode, or use an admin account to preview.

### Cart Missing During REST API Requests

When WordPress renders nav menu widgets via the REST API (`/wp-json/wp/v2/widget-types/`), the cart output is suppressed to prevent it from appearing inside widget block renders.

This is expected. The cart will appear normally on the frontend.

---

## Cart Appears Empty After Back Button Navigation

**Symptom:** User adds products to the cart, navigates to checkout, then uses the browser back button — the cart drawer shows as empty.

**Fix (v1.0.7):** This was fixed by properly refreshing the cart state when navigating back via browser history. Update to v1.0.7 or later.

---

## Tax Display Issues

**Symptom:** Tax amounts not showing correctly in the cart total, or the cart total includes incorrect tax values.

**Fix (v1.0.7):** A tax calculation issue was fixed in the cart total display to ensure accurate tax calculation and display for WooCommerce carts. Update to v1.0.7 or later.

**If still encountering issues:**
- Verify WooCommerce tax settings under **WooCommerce → Settings → Tax**
- Check if a third-party plugin is modifying `woocommerce_before_calculate_totals` at a conflicting priority
- Modern Cart hooks `woocommerce_before_calculate_totals` at priority 10 for custom price support

---

## Product Names Showing HTML Tags (TranslatePress)

**Symptom:** Cart item product names display raw HTML tags when TranslatePress is active.

**Fix (v1.0.6):** Fixed in v1.0.6. Update to resolve.

---

## AJAX Add-to-Cart Not Working

**Symptom:** Clicking "Add to Cart" refreshes the page instead of opening the drawer.

**Checklist:**
1. Verify `enable_ajax_add_to_cart` is `true` in **General settings**
2. Check browser console for JavaScript errors
3. Verify the nonce is present in `moderncart_ajax_object.ajax_nonce`
4. Check for JavaScript conflicts from other plugins (test with plugins deactivated one by one)

**If intentionally disabled by a filter:**
```php
// Check if this filter is returning true somewhere in your codebase
apply_filters( 'moderncart_disable_ajax_add_to_cart', false )
```

**Fix (v1.0.6):** An AJAX add-to-cart issue was fixed to ensure the form submits correctly. Update to v1.0.6 or later.

---

## Coupon Field Issues

**Symptom:** Coupon field doesn't expand, or coupon state is lost after page interactions.

**Fix (v1.0.2):** Coupon field reliability was improved through better state management. Update to v1.0.2 or later.

**Manual check:**
1. Navigate to **WooCommerce → Modern Cart → Cart tab**
2. Verify `enable_coupon_field` is not set to `'disabled'`
3. Ensure the coupon section option is set to `'minimize'` or `'expand'`

---

## Shipping Calculation Errors

**Symptom:** Shipping total shows incorrectly, or changes when cart items change.

**Fix (v1.0.2):** Corrected shipping calculation errors for accurate totals when shipping settings change. Update to v1.0.2 or later.

**Debugging:**
1. Check WooCommerce → Settings → Shipping for correct zone configuration
2. `Cart::get_free_shipping_amount()` reads from Zone ID `1` for unknown customers — ensure Zone 1 has the correct settings
3. Override the threshold: `add_filter('moderncart_free_shipping_min_amount', function() { return 50; });`

---

## Astra Cart Icon Still Triggering Astra's Cart

**Symptom:** Clicking the Astra header cart icon opens Astra's mini-cart instead of Modern Cart.

**Fix (v1.0.2):** Astra cart icon compatibility was fixed through better custom trigger selector handling. Update to v1.0.2 or later.

**Manual check:**
- Verify the `modern-cart-for-wc-available` class is added to the Astra cart menu element (inspect element in browser)
- Check if a caching plugin is serving old HTML without the class

---

## Astra's Mobile Cart Flyout Still Appearing

**Symptom:** Astra's mobile slide-out cart appears alongside Modern Cart's drawer.

Modern Cart should automatically remove Astra's mobile cart flyout on `wp_loaded`. If it's still appearing:

1. Check if `Astra_Builder_Header::mobile_cart_flyout` is being re-added by another plugin or hook
2. Verify Modern Cart is loading after Astra (it should, as it loads on `plugins_loaded`)

---

## Firefox CSS Issues in Cart

**Symptom:** Cart layout looks incorrect in Firefox (misaligned elements, wrong padding).

**Fix (v1.0.2):** Firefox CSS issues in the popup cart were fixed. Update to v1.0.2 or later.

---

## Pro Plugin Compatibility Notice

**Symptom:** An admin notice says "Modern Cart for WooCommerce: Update Required."

**Cause:** Modern Cart Pro (`modern-cart-woo`) is installed but its version is older than 1.2.0.

**Fix:** Update Modern Cart Pro to version 1.2.0 or higher. The Starter plugin will not initialise until Pro is updated.

---

## Cart Not Showing in WooCommerce "Coming Soon" Mode

**Symptom:** Store is in "Coming Soon" mode with "Apply to store pages only" option active, and cart was not appearing correctly.

**Fix (v1.0.4):** A compatibility issue was fixed. Update to v1.0.4 or later.

**Note:** By design, Modern Cart hides the floating button for non-admin users when the site is in full Coming Soon mode.

---

## Settings Not Saving

**Symptom:** Changes in the settings panel don't persist after saving.

**Checklist:**
1. Ensure the currently logged-in user has `manage_options` capability
2. Check browser console for AJAX errors (403, 500)
3. Verify nonce is not expired (happens with very long admin sessions without page reload)
4. Check if a security plugin is blocking `admin-ajax.php` requests

---

## Cart Count Badge Wrong

**Symptom:** Floating cart button shows wrong item count.

The count is driven by `WC()->cart->get_cart_contents_count()`. Check:
- If a plugin is modifying `woocommerce_cart_contents_count`
- Override via filter: `add_filter('moderncart_filter_cart_count', function($count) { return $count; });`

---

## Custom CSS Variables Not Working

**Symptom:** Changed colours in Appearance settings but frontend cart still shows old colours.

**Checklist:**
1. Colour settings are output as CSS custom properties on `:root`. Check browser DevTools → Elements → `:root` for `--moderncart-*` variables
2. If using a caching plugin, clear the cache after changing settings
3. If Astra is active, colours may be sourced from Astra's CSS variables, not the settings — this is by design

---

## General Debugging

### Enable Debug Mode

Add to `wp-config.php`:

```php
define( 'MODERNCART_DEBUG', true );
```

This causes script/style versions to include a timestamp, bypassing browser cache on every load.

### Check Plugin Status

```php
// In a test PHP snippet:
if ( defined( 'MODERNCART_VER' ) ) {
    echo 'Modern Cart loaded. Version: ' . MODERNCART_VER;
    echo 'WooCommerce active: ' . ( class_exists('woocommerce') ? 'yes' : 'no' );
    echo 'Pro status: ' . \ModernCart\Inc\Helper::get_pro_status();
    echo 'Global enabled: ' . ( \ModernCart\Inc\Cart::get_instance()->is_global_enabled() ? 'yes' : 'no' );
}
```

### Verify AJAX Object

In the browser console on a frontend page:

```javascript
console.log(moderncart_ajax_object);
// Should show: { ajax_url, ajax_nonce, animation_speed, ... }
```

If undefined, the scripts failed to enqueue — check `is_global_enabled()` result.

---

## FAQ

### Does Modern Cart work without WooCommerce?
No. WooCommerce is required. Modern Cart only loads after confirming `class_exists('woocommerce')`.

### Does Modern Cart work on the checkout page?
No, by design. The cart is suppressed on checkout pages to avoid UX conflicts.

### Can I use Modern Cart with any page builder?
Yes. It works with Elementor, Spectra, Gutenberg, Beaver Builder, Bricks, Oxygen, and others.

### Does Modern Cart slow down my site?
Scripts are only enqueued when the cart is enabled (`is_global_enabled()` check). The AJAX pattern replaces only the drawer innerHTML rather than full page reloads. Knowledge base data is cached for 12 hours.

### Do I need CartFlows?
No, but the plugin integrates with CartFlows for express checkout and the edit cart button.

### How do I change the "Powered by Modern Cart" footer link?
Go to **WooCommerce → Modern Cart → General** and toggle off "Enable Powered By". Or programmatically:

```php
add_filter( 'moderncart_default_settings', function( $defaults ) {
    $defaults[ MODERNCART_MAIN_SETTINGS ]['enable_powered_by']['value'] = false;
    return $defaults;
} );
```

### Where is support?

- Documentation: `https://cartflows.com/docs-category/modern-cart-for-woocommerce/`
- Support: `https://cartflows.com/support/`
- Community: `https://www.facebook.com/groups/cartflows/`
- GitHub Issues: `https://github.com/brainstormforce/modern-cart/issues`
