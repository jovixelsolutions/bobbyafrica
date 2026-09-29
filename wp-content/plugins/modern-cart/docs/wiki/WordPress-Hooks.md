# WordPress Hooks

A complete reference of all actions, filters, and AJAX handlers registered by Modern Cart. All hook names are prefixed with `moderncart_` unless they are WordPress/WooCommerce core hooks being hooked into.

---

## Actions Registered (add_action)

### Plugin Loader (`plugin-loader.php`)

| Hook | Callback | Priority | Description |
|------|----------|----------|-------------|
| `admin_init` | `Plugin_Loader::redirect_to_onboarding` | default | Handles first-activation redirect to onboarding page |
| `init` | `Plugin_Loader::save_version_info` | default | Saves current/previous version to `moderncart_version` option |
| `plugins_loaded` | `Plugin_Loader::load_classes` | default | Instantiates all feature classes after WooCommerce check |
| `before_woocommerce_init` | `Plugin_Loader::declare_woo_hpos_compatibility` | default | Declares HPOS compatibility via `FeaturesUtil` |

### Admin Menu (`admin-core/admin-menu.php`)

| Hook | Callback | Priority | Description |
|------|----------|----------|-------------|
| `admin_menu` | `Admin_Menu::settings_page` | 99 | Registers "Modern Cart" submenu under WooCommerce |
| `admin_enqueue_scripts` | `Admin_Menu::settings_page_scripts` | default | Enqueues React settings app (only on `woocommerce_page_moderncart_settings`) |
| `wp_ajax_moderncart_update_settings` | `Admin_Menu::moderncart_update_settings` | — | Saves settings (admin only) |
| `wp_ajax_moderncart_fetch_whats_new` | `Admin_Menu::fetch_whats_new` | — | Proxies changelog RSS (admin only) |
| `wp_ajax_moderncart_complete_onboarding` | `Admin_Menu::complete_onboarding` | — | Completes onboarding wizard (admin only) |

### Scripts (`inc/scripts.php`)

| Hook | Callback | Priority | Description |
|------|----------|----------|-------------|
| `wp_enqueue_scripts` | `Scripts::enqueue_scripts` | default | Registers and enqueues `cart.css`, `cart.js`, localizes `moderncart_ajax_object` |
| `wp_enqueue_scripts` | `Scripts::dynamic_styles` | default | Inlines CSS custom properties (colour vars, dimensions) |

### Floating Cart (`inc/floating.php`)

| Hook | Callback | Priority | Description |
|------|----------|----------|-------------|
| `wp_footer` | `Floating::floating_cart` | default | Outputs floating cart button HTML via template |
| `wp_loaded` | `Floating::disable_astra_mobile_slideout` | default | Removes Astra's mobile cart flyout when Modern Cart is active |

### Floating Ajax (`inc/floating-ajax.php`)

| Hook | Callback | Priority | Description |
|------|----------|----------|-------------|
| `wp_ajax_moderncart_refresh_floating_cart` | `Floating_Ajax::refresh_floating_cart` | — | Refreshes floating button HTML (logged-in) |
| `wp_ajax_nopriv_moderncart_refresh_floating_cart` | `Floating_Ajax::refresh_floating_cart` | — | Refreshes floating button HTML (logged-out) |

### Slide Out Cart (`inc/slide-out.php`)

| Hook | Callback | Priority | Description |
|------|----------|----------|-------------|
| `wp_footer` | `Slide_Out::slide_out` | default | Outputs slide-out drawer wrapper HTML |
| `moderncart_slide_out_content` | `Slide_Out::render_header` | default | Renders cart header inside drawer |
| `moderncart_slide_out_content` | `Slide_Out::render_contents` | default | Renders cart item list inside drawer |
| `moderncart_slide_out_header_before` | `Slide_Out::render_free_shipping_bar` | default | Renders free shipping progress bar |
| `moderncart_slide_out_content` | `Slide_Out::render_footer` | 15 | Renders cart footer (totals area) |
| `moderncart_slide_out_footer_content` | `Slide_Out::render_coupon_form` | 25 | Renders coupon input form |
| `moderncart_slide_out_footer_content` | `Slide_Out::render_totals` | 35 | Renders cart totals and checkout button |
| `moderncart_slide_out_cart_after` | `Slide_Out::render_empty_cart_recommendations` | default | Renders empty cart product recommendations |
| `moderncart_slide_out_coupon_form_after` | `Slide_Out::render_coupon_removal` | default | Renders applied coupon tags with remove buttons |
| `woocommerce_before_calculate_totals` | `Slide_Out::set_custom_prices` | 10 | Applies any `custom_price` from cart item meta |

### Slide Out Ajax (`inc/slide-out-ajax.php`)

| Hook | Callback | Priority | Description |
|------|----------|----------|-------------|
| `wp_ajax_moderncart_refresh_slide_out_cart` | `Slide_Out_Ajax::refresh_slide_out_cart` | — | Refreshes full drawer (logged-in) |
| `wp_ajax_nopriv_moderncart_refresh_slide_out_cart` | `Slide_Out_Ajax::refresh_slide_out_cart` | — | Refreshes full drawer (logged-out) |
| `wp_ajax_moderncart_remove_product` | `Slide_Out_Ajax::remove_product` | — | Removes product from cart (logged-in) |
| `wp_ajax_nopriv_moderncart_remove_product` | `Slide_Out_Ajax::remove_product` | — | Removes product from cart (logged-out) |
| `wp_ajax_moderncart_update_cart` | `Slide_Out_Ajax::update_cart` | — | Updates product quantity (logged-in) |
| `wp_ajax_nopriv_moderncart_update_cart` | `Slide_Out_Ajax::update_cart` | — | Updates product quantity (logged-out) |
| `wp_ajax_moderncart_apply_coupon` | `Slide_Out_Ajax::apply_coupon` | — | Applies coupon code (logged-in) |
| `wp_ajax_nopriv_moderncart_apply_coupon` | `Slide_Out_Ajax::apply_coupon` | — | Applies coupon code (logged-out) |
| `wp_ajax_moderncart_remove_coupon` | `Slide_Out_Ajax::remove_coupon` | — | Removes coupon code (logged-in) |
| `wp_ajax_nopriv_moderncart_remove_coupon` | `Slide_Out_Ajax::remove_coupon` | — | Removes coupon code (logged-out) |
| `wp_ajax_moderncart_add_to_cart` | `Slide_Out_Ajax::add_to_cart` | — | Adds product to cart (logged-in) |
| `wp_ajax_nopriv_moderncart_add_to_cart` | `Slide_Out_Ajax::add_to_cart` | — | Adds product to cart (logged-out) |

---

## Filters Registered (add_filter)

### Plugin Loader

| Hook | Callback | Priority | Description |
|------|----------|----------|-------------|
| `plugin_action_links_{MODERNCART_BASE}` | `Plugin_Loader::action_links` | default | Adds Settings link to Plugins list page |

### Floating Cart

| Hook | Callback | Priority | Description |
|------|----------|----------|-------------|
| `astra_cart_in_menu_class` | `Floating::modify_mini_cart_classes` | default | Adds `modern-cart-for-wc-available` CSS class to Astra menu cart |
| `astra_get_option_woo-header-cart-click-action` | `Floating::modify_astra_slideout` | default | Returns empty string to disable Astra's slide-in cart |
| `astra_get_option_shop-add-to-cart-action` | `Floating::disable_astra_slideout` | 10 | Disables Astra's slide-in cart on shop pages |

### Slide Out Cart

| Hook | Callback | Priority | Description |
|------|----------|----------|-------------|
| `cpsw_express_checkout_selected_location_status` | `Slide_Out::express_checkout_location_status` | default | Enables CartFlows express checkout in the cart drawer |
| `cpsw_express_checkout_allow_custom_pages` | `Slide_Out::express_checkout_show_all_pages` | default | Allows CartFlows express checkout on all pages when Modern Cart is enabled |

---

## Custom Actions (do_action)

These hooks fire within Modern Cart code and are available for third-party extensions:

| Hook | Fired In | Description |
|------|----------|-------------|
| `moderncart_loaded` | `Plugin_Loader::__construct()` | Fires immediately after Plugin_Loader is constructed. Use to hook in early. |
| `moderncart_slide_out_content` | `shop/slide-out.php` template | Fires inside the drawer. Registered callbacks render header, contents, footer. |
| `moderncart_slide_out_header_before` | Inside `render_header()` | Fires before the header — used for the free shipping bar. |
| `moderncart_slide_out_footer_content` | Inside `render_footer()` | Fires inside the footer — used for coupon form and totals. |
| `moderncart_slide_out_cart_after` | Inside `render_contents()` | Fires after cart items list — used for empty cart recommendations. |
| `moderncart_slide_out_coupon_form_after` | `cart/coupon-form.php` template | Fires after coupon form — used for applied coupon removal UI. |
| `moderncart_woocommerce_ajax_added_to_cart` | `Slide_Out_Ajax::add_to_cart()` | Fires after a product is added via AJAX — mirrors WooCommerce's hook. |
| `moderncart_woocommerce_after_cart_item_name` | `Slide_Out::get_product_name()` | Fires after product name output in cart item. |
| `cpsw_payment_request_button_before` | `Slide_Out::action_request_button_before()` | Fires before CartFlows express checkout button — adds "OR" separator. |

---

## Custom Filters (apply_filters)

### Enabling / Disabling Features

| Filter | Default | Description |
|--------|---------|-------------|
| `moderncart_override_is_global_enabled` | `null` | Return `true`/`false` to force-enable or force-disable the cart globally. |
| `moderncart_disable_ajax_add_to_cart` | `false` | Return `true` to disable AJAX add-to-cart. |
| `moderncart_redirect_after_add_to_cart` | `false` | Return a URL string to redirect after add-to-cart instead of opening drawer. |
| `modern_cart_disable_nocache_headers` | `false` | Return `true` to disable the `no-store` cache headers on AJAX responses. |

### Appearance

| Filter | Default | Description |
|--------|---------|-------------|
| `moderncart_css_vars` | Array of CSS var key/values | Modify the CSS custom properties injected in `:root` on the frontend. |
| `moderncart_default_settings` | Full defaults array | Override any default setting value. |

### Cart Behaviour

| Filter | Default | Description |
|--------|---------|-------------|
| `moderncart_checkout_button_url` | `wc_get_checkout_url()` | Override the checkout button URL. |
| `moderncart_empty_cart_button_url` | WooCommerce shop page URL | Override the "shop now" URL shown when cart is empty. |
| `moderncart_empty_cart_message` | Translated "Your cart is empty. Shop now" | Override the empty cart button text. |
| `moderncart_empty_cart_recommendation_limit` | `5` | Override how many products show in empty-cart recommendations. |
| `moderncart_order_summary_style` | `'style1'` | Override the order summary style (`'style1'` or `'style2'`). |
| `moderncart_filter_cart_count` | `WC cart contents count` | Filter the cart item count shown on the floating button. |
| `moderncart_free_shipping_min_amount` | Calculated from shipping zone | Override the free shipping threshold amount. |
| `moderncart_enable_shipping` | `true` | Return `false` to hide shipping row in totals. |
| `moderncart_enable_tax` | `true` | Return `false` to hide tax row in totals. |
| `moderncart_enable_subtotal` | `true` | Return `false` to hide subtotal row in totals. |
| `moderncart_enable_discount` | `true` | Return `false` to hide discount row in totals. |
| `moderncart_enable_total` | `true` | Return `false` to hide total row in totals. |

### HTML / Output

| Filter | Default | Description |
|--------|---------|-------------|
| `moderncart_modal_slide_out_classes` | Array of CSS classes | Modify classes on the outermost drawer `<div>`. |
| `moderncart_slide_out_classes` | Array of CSS classes | Modify classes on the inner drawer `<div>`. |
| `moderncart_slide_out_header_classes` | Array of CSS classes | Modify classes on the drawer header. |
| `moderncart_slide_out_cart_item_classes` | `['moderncart-cart-item']` | Modify classes on each cart item row. |
| `moderncart_slide_out_cart_totals_classes` | Array of CSS classes | Modify classes on the totals container. |
| `moderncart_slide_out_coupon_form_classes` | Array of CSS classes | Modify classes on the coupon form. |
| `moderncart_slide_out_empty_cart_classes` | Array of CSS classes | Modify classes on the empty state display. |
| `moderncart_slide_out_empty_cart_recommendations_classes` | Array of CSS classes | Modify classes on empty-cart recommendations. |
| `moderncart_floating_cart_launcher_classes` | `['moderncart-toggle-slide-out']` | Modify classes on the floating cart trigger element. |
| `moderncart_powered_by_classes` | `['moderncart-powered-by']` | Modify classes on the "Powered by" element. |
| `moderncart_single_line_item_classes` | Array of CSS classes | Modify classes on each totals line item. |
| `moderncart_cart_totals_subtotal_html` | Subtotal HTML | Filter the subtotal value HTML. |
| `moderncart_cart_totals_discount_total_html` | Discount HTML | Filter the discount value HTML. |
| `moderncart_cart_totals_shipping_html` | Shipping HTML | Filter the shipping value HTML. |
| `moderncart_cart_totals_vat_total_html` | Tax HTML | Filter the tax value HTML. |
| `moderncart_cart_totals_order_total_html` | Total HTML | Filter the order total HTML. |

### Template System

| Filter | Default | Description |
|--------|---------|-------------|
| `moderncart_get_template_part_args` | Args array | Filter args before they are extracted into template scope. |
| `moderncart_set_template_path` | Plugin `templates/` dir | Override the base template directory. |
| `moderncart_get_template_part` | Template file path | Override the resolved template file path. |

### Product / Cart Item

| Filter | Default | Description |
|--------|---------|-------------|
| `moderncart_is_valid_product` | `true` | Return `false` to prevent a product ID from being fetched. |
| `moderncart_get_product_by_id` | WC product object | Override the product object returned for a given ID. |
| `moderncart_woocommerce_cart_item_product` | Cart item product data | Filter the product object used for a cart item. |
| `moderncart_woocommerce_cart_item_product_id` | Product ID from cart item | Filter the product ID used for a cart item. |
| `moderncart_woocommerce_cart_item_permalink` | Product permalink | Filter the permalink used for the cart item product link. |
| `moderncart_woocommerce_cart_item_name` | Product name | Filter the product name HTML in the cart item. |
| `moderncart_woocommerce_cart_item_thumbnail` | Product image HTML | Filter the product image output in the cart item. |
| `moderncart_woocommerce_cart_item_subtotal` | Subtotal price HTML | Filter the per-item subtotal HTML. |
| `moderncart_after_cart_item_name_hook_collapsible` | `true` | Return `false` to disable the "View details" collapsible for variation data. |
| `moderncart_after_cart_item_name_price` | `false` | Return `true` to show a single-unit price below the product name. |
| `moderncart_cart_item_removed_title` | Product name in quotes | Filter the removed item name shown in the undo notification. |
| `moderncart_woocommerce_after_cart_item_name` | — | Action: fires after product name. |
| `moderncart_woocommerce_quantity_input_args` | Args array | Filter all quantity input attributes. |
| `moderncart_woocommerce_quantity_input_classes` | `['input-text', 'qty', 'text']` | Filter quantity input CSS classes. |
| `moderncart_woocommerce_quantity_input_max` | `-1` (unlimited) | Override max quantity. |
| `moderncart_woocommerce_quantity_input_min` | `0` | Override min quantity. |
| `moderncart_woocommerce_quantity_input_step` | `1` | Override quantity step. |

### Admin

| Filter | Default | Description |
|--------|---------|-------------|
| `moderncart_settings_admin_localize_script` | Full localize array | Filter all data passed to the React admin app via `wp_localize_script`. |
| `moderncart_settings_db_values` | Filtered DB values | Filter settings values retrieved from DB before merging with defaults. |
| `moderncart_admin_version_badge_info` | `['label' => 'Free', 'title' => MODERNCART_VER]` | Filter the version badge shown in the admin header. |
| `moderncart_cpsw_plugin_action_links` | Settings link array | Filter plugin action links added on the Plugins page. |
