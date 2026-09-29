# WooCommerce Integration

CartFlows is built on top of WooCommerce. It overrides WooCommerce checkout templates, hooks into the cart and order lifecycle, and adds product-level settings for funnel routing.

## Requirements

| Requirement | Minimum | Tested Up To |
|-------------|---------|-------------|
| WooCommerce | 3.0+ | 9.8.5 |

CartFlows checks for WooCommerce on activation and displays a notice if it is not installed or active.

## Template Overrides

CartFlows overrides WooCommerce templates placed in the `woocommerce/` directory. This follows the standard WooCommerce template override pattern — CartFlows templates take precedence over the active theme's WooCommerce templates.

```
cartflows/woocommerce/
├── checkout/
│   ├── form-checkout.php   ← Main checkout form template
│   └── review-order.php    ← Order review table
└── ...
```

CartFlows replaces the standard WooCommerce checkout with its own layout system (Modern Checkout, Instant Checkout, etc.) by replacing these template files.

## Checkout Page Replacement

When a visitor accesses a `cartflows_step` post of type `checkout`, CartFlows:

1. Detects the active page builder and loads the appropriate template
2. Enqueues checkout-specific scripts and styles
3. Fires `cartflows_checkout_scripts` for additional assets
4. Renders the checkout form via the WooCommerce `[woocommerce_checkout]` shortcode (or CartFlows equivalent)
5. Applies layout-specific CSS (Modern/Instant checkout styles)

## WooCommerce Hook Integration

CartFlows hooks into WooCommerce's standard hooks to control cart and order behaviour:

### Cart Management

```php
// Control which products are in the cart for a checkout step
add_filter( 'cartflows_selected_checkout_products', function( $products, $checkout_id ) {
    return $products;
}, 10, 2 );
```

CartFlows can pre-populate the cart with specific products based on the checkout step configuration (`wcf-enable-product-options` meta).

### Post-Purchase Redirect

After a successful WooCommerce order:

1. CartFlows hooks into `woocommerce_thankyou` (or similar)
2. Checks if the order came through a CartFlows checkout step
3. Redirects to the configured next step (upsell, downsell, or thank you page)

The redirect target is filterable:

```php
add_filter( 'cartflows_checkout_next_step_id', function( $step_id, $order, $checkout_id ) {
    return $step_id; // Modify to redirect elsewhere
}, 10, 3 );
```

### WooCommerce `wc_clean` Sanitiser

CartFlows uses `wc_clean()` for sanitising WooCommerce-specific data. This function handles:
- Strings: equivalent to `sanitize_text_field()`
- Arrays: recursively applies `wp_kses_post()` to values

It is registered as a custom sanitiser in `phpcs.xml.dist` so PHPCS recognises it.

## Checkout Layout Types

CartFlows supports multiple checkout layouts:

| Layout | Class | Description |
|--------|-------|-------------|
| Standard | `class-cartflows-checkout-markup.php` | Default WooCommerce-style layout |
| Modern | `layouts/class-cartflows-modern-checkout.php` | Two-column modern layout |
| Instant | `layouts/class-cartflows-instant-checkout.php` | Full-screen distraction-free layout |

The active layout is stored in step post meta (`wcf-checkout-layout`) and the template is resolved via the `cartflows_page_template` filter.

## Order Bump (Pre-Purchase Add-On)

An order bump is an additional product offered on the checkout page, typically displayed as a checkbox or highlighted box:

- Configured via step meta: `wcf-order-bump`
- Rendered within the checkout form
- Submits as an additional cart item
- Revenue tracked separately in analytics (`BumpOfferRevenue`, `BumpConversions`)

## Store Checkout (Global Checkout)

The **Store Checkout** feature (`modules/checkout/class-cartflows-global-checkout.php`) replaces the default WooCommerce checkout page with a CartFlows-designed checkout step:

- Enabled/disabled via `cartflows_update_store_checkout_status` AJAX
- Fires `cartflows_after_save_store_checkout` after status changes
- When enabled, all WooCommerce "Proceed to Checkout" buttons send customers through CartFlows

## WooCommerce Dynamic Flow

The **WooCommerce Dynamic Flow** module (`modules/woo-dynamic-flow/`) enables product-based automatic funnel routing:

- Each WooCommerce product can be assigned a CartFlows flow via the `cartflows_redirect_flow_id` product meta
- When that product is added to the cart and the customer proceeds to checkout, they are automatically redirected through the assigned funnel
- Shortcodes allow embedding flow links on product or category pages

## Product-Level CartFlows Settings

CartFlows adds a settings tab to each WooCommerce product edit page:

| Setting | Meta Key | Description |
|---------|----------|-------------|
| Redirect to flow | `cartflows_redirect_flow_id` | Auto-redirect to funnel when this product is purchased |
| Custom button text | `cartflows_add_to_cart_text` | Override "Add to Cart" button text |

## Checkout Field Management

CartFlows provides granular control over WooCommerce checkout fields:

- Enable/disable individual billing and shipping fields
- Set field width (full/half column)
- Customise labels and placeholders
- Set required/optional status

Field settings are stored as individual post meta keys on the checkout step (pattern: `wcf-{field_name}`).

## Analytics Integration

CartFlows tracks WooCommerce-specific metrics:

| Metric | Description |
|--------|-------------|
| Conversion rate | Orders / unique visitors to checkout |
| Revenue per visit | Total revenue / total step visits |
| Average order value | Total revenue / total orders |
| Offer conversions | Upsell/downsell acceptance rate |
| Bump conversions | Order bump acceptance rate |

Tracking is done server-side using WooCommerce order data stored in post meta on CartFlows steps.

## Compatibility

CartFlows maintains compatibility with popular WooCommerce extensions via the `compatibilities/` directory. When a known conflicting plugin is detected, CartFlows applies targeted fixes.

See [Page-Builder-Integrations](Page-Builder-Integrations) for page builder compatibility.

## Related Pages

- [Feature-Modules](Feature-Modules)
- [Database-Schema](Database-Schema)
- [WordPress-Hooks-Reference](WordPress-Hooks-Reference)
- [Architecture-Overview](Architecture-Overview)
