# WordPress Hooks Reference

CartFlows provides an extensive hook system using the `cartflows_` prefix for all actions and filters. Third-party plugins and CartFlows Pro use these hooks to extend functionality.

## Actions (do_action)

### Plugin Lifecycle

| Hook | Arguments | Description |
|------|-----------|-------------|
| `cartflows_loaded` | — | Plugin is fully bootstrapped. Safe to hook functionality that requires CartFlows to be ready. |
| `cartflows_update_before` | — | Fires before an automatic plugin update runs. |
| `cartflows_update_after` | — | Fires after an automatic plugin update completes. |

### Frontend Page Hooks

| Hook | Arguments | Description |
|------|-----------|-------------|
| `cartflows_wp` | `$step_id` | Fires on `wp` action when the current page is a CartFlows step. |
| `cartflows_wp_footer` | — | Fires in the footer of CartFlows step pages. |

### Checkout Page Hooks

| Hook | Arguments | Description |
|------|-----------|-------------|
| `cartflows_checkout_scripts` | — | Enqueue checkout-specific scripts. |
| `cartflows_checkout_form_before` | `$checkout_id` | Before the checkout form renders. |
| `cartflows_checkout_cart_empty` | `$checkout_id` | When the cart is empty on a checkout step. |
| `cartflows_add_before_main_section` | `$checkout_layout` | Before the main checkout container. |
| `cartflows_add_after_main_section` | — | After the main checkout container. |
| `cartflows_primary_container_bottom` | — | At the bottom of the primary checkout container. |
| `cartflows_checkout_before_shortcode` | `$checkout_id` | Before rendering checkout shortcode (editor preview). |

### Thank You Page Hooks

| Hook | Arguments | Description |
|------|-----------|-------------|
| `cartflows_thank_you_scripts` | — | Enqueue thank you page scripts. |
| `cartflows_woocommerce_order_overview_cancelled` | `$order` | When an order is cancelled on the thank you page. |

### Opt-in Hooks

| Hook | Arguments | Description |
|------|-----------|-------------|
| `cartflows_optin_before_shortcode` | `$checkout_id` | Before rendering optin shortcode (editor preview). |

### Elementor Integration Hooks

| Hook | Arguments | Description |
|------|-----------|-------------|
| `cartflows_elementor_before_checkout_shortcode` | `$checkout_id` | Before Elementor checkout widget renders. |
| `cartflows_elementor_before_optin_shortcode` | `$checkout_id` | Before Elementor optin widget renders. |
| `cartflows_elementor_checkout_options_filters` | `$settings` | After Elementor checkout widget options are applied. |
| `cartflows_elementor_optin_options_filters` | `$settings` | After Elementor optin widget options are applied. |
| `cartflows_elementor_editor_compatibility` | `$post_id, $elementor_ajax` | During Elementor editor compatibility checks. |

### Admin AJAX Hooks

| Hook | Arguments | Description |
|------|-----------|-------------|
| `cartflows_admin_save_step_meta` | `$step_id` | After step meta settings are saved via AJAX. Use to trigger side effects. |
| `cartflows_admin_save_global_settings` | `$setting_tab, $nonce_action` | After global settings are saved. |
| `cartflows_admin_after_delete_flow` | `$flow_id` | After a flow is permanently deleted. |
| `cartflows_after_save_store_checkout` | — | After store checkout status is updated. |

### Template Importer Hooks

| Hook | Arguments | Description |
|------|-----------|-------------|
| `cartflows_import_complete` | — | After a template import batch completes. |
| `cartflows_after_template_import` | `$new_step_id, $response` | After a single template is imported into a step. |
| `cartflows_site_import_batch_complete` | — | After a full site template import batch finishes. |

### Tracking / Analytics Hooks

| Hook | Arguments | Description |
|------|-----------|-------------|
| `cartflows_facebook_pixel_events` | — | Output custom Facebook Pixel events. |
| `cartflows_google_analytics_events` | — | Output custom Google Analytics events. |
| `cartflows_tiktok_pixel_events` | — | Output custom TikTok Pixel events. |
| `cartflows_google_ads_events` | — | Output custom Google Ads events. |
| `cartflows_pinterest_tag_events` | — | Output custom Pinterest Tag events. |
| `cartflows_snapchat_pixel_events` | — | Output custom Snapchat Pixel events. |

---

## Filters (apply_filters)

### Admin Settings Filters

| Hook | Arguments | Returns | Description |
|------|-----------|---------|-------------|
| `cartflows_admin_global_data_options` | `$options` | `$options` | Modify global settings data sent to the settings app. |
| `cartflows_admin_flows_step_data` | `$steps` | `$steps` | Modify step data in the flow API response. |
| `cartflows_admin_action_slug` | `$slug, $flow_id` | string | Change the edit step action slug. |
| `cartflows_admin_localized_vars` | `$vars` | `$vars` | Modify JavaScript variables localised to admin pages. |
| `cartflows_admin_save_global_settings` | Fires as action | — | See actions above. |

### Admin Field Definition Filters

| Hook | Arguments | Returns | Description |
|------|-----------|---------|-------------|
| `cartflows_admin_{type}_step_default_meta_fields` | `$fields, $step_id` | `$fields` | Modify default meta fields for a step type. Replace `{type}` with the step type (checkout, optin, etc.) |
| `cartflows_admin_flow_settings` | `$settings` | `$settings` | Modify flow-level settings definitions. |
| `cartflows_user_role_default_settings` | `$settings` | `$settings` | Modify default user role settings. |
| `cartflows_facebook_settings_default` | `$settings` | `$settings` | Modify default Facebook integration settings. |
| `cartflows_pinterest_settings_default` | `$settings` | `$settings` | Modify default Pinterest integration settings. |

### Frontend / Page Rendering Filters

| Hook | Arguments | Returns | Description |
|------|-----------|---------|-------------|
| `cartflows_remove_theme_styles` | `true` | bool | Control whether theme stylesheets are removed on CartFlows pages. Return `false` to keep theme styles. |
| `cartflows_remove_theme_scripts` | `true` | bool | Control whether theme scripts are removed on CartFlows pages. |
| `cartflows_load_min_assets` | `false` | bool | Return `true` to use minified assets (useful in dev). |
| `cartflows_checkout_form_layout` | `$layout` | string | Modify the CSS classes applied to the checkout form wrapper. |
| `cartflows_page_template` | `$template` | string | Override the page template file used for a step. |
| `cartflows_is_compatibility_theme` | `$bool` | bool | Override whether the current theme triggers compatibility mode. |
| `cartflows_maybe_load_font_awesome` | `true` | bool | Control Font Awesome loading on CartFlows pages. |
| `cartflows_do_not_cache_step` | `true, $step_id` | bool | Return `false` to allow caching plugins to cache a specific step. |
| `cartflows_enable_append_query_string` | `false` | bool | Enable passing query strings between funnel steps. |
| `cartflows_may_be_append_query_strings_args` | `$strings` | array | Modify the query strings passed between funnel steps. |

### WooCommerce / Cart Filters

| Hook | Arguments | Returns | Description |
|------|-----------|---------|-------------|
| `cartflows_selected_checkout_products` | `$products, $checkout_id` | array | Modify the products added to the cart for a checkout step. |
| `cartflows_checkout_next_step_id` | `$step_id, $order, $checkout_id` | int | Override which step the customer is redirected to after checkout. |
| `cartflows_checkout_cart_empty_message` | `$message` | string | Customise the empty cart message on checkout pages. |
| `cartflows_auto_prefill_checkout_fields` | `true` | bool | Disable auto-filling of checkout fields from customer data. |
| `cartflows_supported_product_types_for_search` | `$types` | array | Add or remove product types from the product search results. |
| `cartflows_skip_configure_cart` | `false` | bool | Skip cart configuration in the WooCommerce Dynamic Flow module. |
| `cartflows_show_coupon_field` | `$show` | bool | Show or hide the coupon code field on checkout pages. |

### Checkout UI Text Filters

| Hook | Arguments | Returns | Description |
|------|-----------|---------|-------------|
| `cartflows_woo_billling_text` | `$text` | string | Customise the billing section heading. |
| `cartflows_woo_your_order_text` | `$text` | string | Customise the "Your Order" section heading. |
| `cartflows_woo_shipping_text` | `$text` | string | Customise the shipping section heading. |

### Analytics / Tracking Filters

| Hook | Arguments | Returns | Description |
|------|-----------|---------|-------------|
| `cartflows_enable_non_sensitive_data_tracking` | `$bool` | bool | Override non-sensitive analytics tracking opt-in. |
| `cartflows_get_specific_stats` | `$data` | array | Modify the analytics stats data sent to the server. |
| `cartflows_view_content_offer` | `$params, $step_id` | array | Modify Facebook view content event params for offer pages. |
| `cartflows_tiktok_view_content_offer` | `$params, $step_id` | array | Modify TikTok view content event params for offer pages. |
| `cartflows_snapchat_view_content_offer` | `$data` | array | Modify Snapchat view content event params. |
| `cartflows_is_offer_type` | `$bool` | bool | Override whether the current step is an offer type. |

### Template / Import Filters

| Hook | Arguments | Returns | Description |
|------|-----------|---------|-------------|
| `cartflows_templates_url` | `$url` | string | Override the CartFlows template library API URL. |
| `cartflows_licence_args` | `[]` | array | Add licence arguments to template API requests. |
| `cartflows_template_import_meta_data` | `$meta` | array | Modify post meta before it is saved during template import. |
| `cartflows_image_importer_skip_image` | `false, $attachment` | bool | Skip importing a specific image during template import. |

### Misc Filters

| Hook | Arguments | Returns | Description |
|------|-----------|---------|-------------|
| `cartflows_enable_setup_wizard` | `$bool` | bool | Control whether the setup wizard is displayed. |
| `cartflows_show_deprecated_step_notes` | `false` | bool | Show notes for deprecated step features. |
| `cartflows_file_mod_disabled` | `$bool` | bool | Override file modification restriction check. |

---

## Usage Examples

### Redirect to a custom page after checkout

```php
add_filter( 'cartflows_checkout_next_step_id', function( $step_id, $order, $checkout_id ) {
    if ( /* custom condition */ ) {
        return get_page_by_path( 'custom-thankyou' )->ID;
    }
    return $step_id;
}, 10, 3 );
```

### Keep theme styles on CartFlows pages

```php
add_filter( 'cartflows_remove_theme_styles', '__return_false' );
```

### Add custom products to checkout

```php
add_filter( 'cartflows_selected_checkout_products', function( $products, $checkout_id ) {
    if ( $checkout_id === 456 ) {
        $products[] = wc_get_product( 789 );
    }
    return $products;
}, 10, 2 );
```

### Run code after a step is saved

```php
add_action( 'cartflows_admin_save_step_meta', function( $step_id ) {
    // Clear your custom cache
    delete_transient( 'my_plugin_step_cache_' . $step_id );
} );
```

---

## Related Pages

- [REST-API-Reference](REST-API-Reference)
- [AJAX-API-Reference](AJAX-API-Reference)
- [WooCommerce-Integration](WooCommerce-Integration)
- [Feature-Modules](Feature-Modules)
- [WordPress-Plugin-Structure](WordPress-Plugin-Structure)
