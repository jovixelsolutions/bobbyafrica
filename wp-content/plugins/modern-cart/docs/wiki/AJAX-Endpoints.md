# AJAX Endpoints

All AJAX endpoints use WordPress's `admin-ajax.php` mechanism. The frontend script (`assets/js/cart.js`) sends requests with a nonce (`moderncart_ajax_nonce`) for CSRF protection. All endpoints support both logged-in (`wp_ajax_`) and logged-out (`wp_ajax_nopriv_`) users.

---

## Authentication

Every endpoint verifies the nonce before processing:

```php
if ( ! isset( $_POST['moderncart_nonce'] ) ||
     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moderncart_nonce'] ) ), 'moderncart_ajax_nonce' ) ) {
    wp_die(); // or return;
}
```

The nonce is created on page load via `wp_create_nonce('moderncart_ajax_nonce')` in `Scripts::enqueue_scripts()` and passed to the frontend via `moderncart_ajax_object.ajax_nonce`.

Cache headers are set on AJAX responses where indicated:
```php
Helper::set_nocache_headers();
// Sets: Cache-Control: no-store, no-cache, must-revalidate, max-age=0
//       Pragma: no-cache
//       Expires: 0
```

---

## Frontend → Cart AJAX

These endpoints are handled by `ModernCart\Inc\Slide_Out_Ajax`.

---

### `moderncart_add_to_cart`

Adds one or more products to the WooCommerce cart.

**Method:** `POST`
**Handler:** `Slide_Out_Ajax::add_to_cart()`

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | yes | `moderncart_add_to_cart` |
| `moderncart_nonce` | string | yes | CSRF nonce |
| `productData` | array | yes | Array of product objects |
| `productData[].productId` | int | yes | WooCommerce product ID |
| `productData[].variationId` | int | no | Variation ID (for variable products) |
| `productData[].quantity` | int | no | Quantity to add (default: 1) |
| `productData[].attributes` | object | no | Variation attribute key→value pairs |

**Response (success):**

```json
{
    "content": "<html string of updated drawer inner content>",
    "redirect_to": "https://example.com/cart" // empty string if no redirect
}
```

**Response (error):**

```json
{
    "success": false,
    "data": { "message": "Product not found" }
}
```

**Notes:**
- Validates that `product_id` is numeric and positive
- Checks `is_sold_individually()` — sends error if product already in cart
- Fires `moderncart_woocommerce_ajax_added_to_cart` action on success
- Returns full drawer inner HTML (`shop/slide-out-inner.php`) with success/error notification

---

### `moderncart_refresh_slide_out_cart`

Refreshes the full slide-out cart content. Called when cart state changes externally.

**Method:** `POST`
**Handler:** `Slide_Out_Ajax::refresh_slide_out_cart()`

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | yes | `moderncart_refresh_slide_out_cart` |
| `moderncart_nonce` | string | yes | CSRF nonce |
| `notice_action` | boolean | no | When `true`, shows "Cart updated successfully!" message |

**Response:**

```json
{
    "content": "<html string of updated drawer inner content>"
}
```

**Notes:**
- Sets no-cache headers
- Returns `shop/slide-out-inner.php` rendered HTML

---

### `moderncart_remove_product`

Removes a single product from the cart. Includes undo ("Undo?" link) in the response.

**Method:** `POST`
**Handler:** `Slide_Out_Ajax::remove_product()`

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | yes | `moderncart_remove_product` |
| `moderncart_nonce` | string | yes | CSRF nonce |
| `cart_key` | string | yes | WooCommerce cart item key (hash) |

**Response (success):**

```json
{
    "success": 1,
    "notice": "\"Product Name\" removed. <a href=\"#\" data-key=\"abc123\" class=\"moderncart-restore-item\">Undo?</a>"
}
```

**Notes:**
- Calls `WC()->cart->remove_cart_item($cart_item_key)`
- Saves removed item title to WC session (`moderncart_last_removed_item_name`) for restore
- Undo link is only shown if product is still in stock and has enough stock
- Clears WooCommerce notices after removal

---

### `moderncart_update_cart`

Updates the quantity of a cart item (or removes it when quantity is 0).

**Method:** `POST`
**Handler:** `Slide_Out_Ajax::update_cart()`

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | yes | `moderncart_update_cart` |
| `moderncart_nonce` | string | yes | CSRF nonce |
| `cart_key` | string | yes | WooCommerce cart item key |
| `quantity` | int | yes | New quantity (0 = remove item) |

**Response:**

```json
{
    "content": "<html string of updated drawer inner content>"
}
```

**Validation:**
- Returns HTTP 403 if `quantity` is not numeric, negative, or `cart_key` is empty
- Checks stock availability (`managing_stock()`, `backorders_allowed()`, `get_stock_quantity()`)
- Checks held stock (`wc_get_held_stock_quantity()`)

**Notes:**
- Quantity `0` → `WC()->cart->remove_cart_item($cart_key)`
- Quantity > 0 and in stock → `WC()->cart->set_quantity($cart_key, $quantity)`
- Returns full drawer inner HTML with success/error notification

---

### `moderncart_apply_coupon`

Applies a coupon code to the WooCommerce cart.

**Method:** `POST`
**Handler:** `Slide_Out_Ajax::apply_coupon()`

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | yes | `moderncart_apply_coupon` |
| `moderncart_nonce` | string | yes | CSRF nonce |
| `coupon` | string | yes | Coupon code to apply |

**Response:**

```json
{
    "content": "<html string of updated drawer inner content>"
}
```

**Validation:**
- Empty coupon → error "Enter a coupon code!"
- Already applied → error "Sorry, this coupon code is already applied!"
- Invalid coupon → uses `WC_Discounts::is_coupon_valid()` for specific error message
- Uses `wc_format_coupon_code()` to normalise input

**Notes:**
- Stores coupon error in WC session (`moderncart_coupon_error`) if `is_wp_error()`
- Clears WooCommerce notices before returning (`wc_clear_notices()`)
- Returns full drawer inner HTML with coupon form forced open on error

---

### `moderncart_remove_coupon`

Removes an applied coupon from the WooCommerce cart.

**Method:** `POST`
**Handler:** `Slide_Out_Ajax::remove_coupon()`

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | yes | `moderncart_remove_coupon` |
| `moderncart_nonce` | string | yes | CSRF nonce |
| `coupon` | string | yes | Coupon code to remove |

**Response:**

```json
{
    "content": "<html string of updated drawer inner content>"
}
```

**Notes:**
- Calls `WC()->cart->remove_coupon($coupon)`
- Recalculates shipping and totals after removal
- Clears WooCommerce notices

---

## Floating Button AJAX

Handled by `ModernCart\Inc\Floating_Ajax`.

---

### `moderncart_refresh_floating_cart`

Refreshes the floating cart button HTML (icon + count badge).

**Method:** `POST`
**Handler:** `Floating_Ajax::refresh_floating_cart()`

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | yes | `moderncart_refresh_floating_cart` |
| `moderncart_nonce` | string | yes | CSRF nonce |

**Response:**

```json
{
    "content": "<html string of floating button inner content>"
}
```

**Notes:**
- Sets no-cache headers
- Returns `shop/floating-inner.php` rendered HTML
- Skips rendering if `floating_cart_position` is `'disabled'`

---

## Admin AJAX Endpoints

Handled by `ModernCart\Admin_Core\Admin_Menu`. These require `manage_options` capability and use a separate nonce.

---

### `moderncart_update_settings`

Saves one or more option groups to `wp_options`.

**Method:** `POST` (admin only)
**Nonce:** `moderncart_update_settings` (key: `security`)
**Capability:** `manage_options`

**Request Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `security` | string | Nonce for `moderncart_update_settings` |
| `moderncart_setting` | string (JSON) | Serialised general settings |
| `moderncart_cart` | string (JSON) | Serialised cart settings |
| `moderncart_floating` | string (JSON) | Serialised floating settings |
| `moderncart_appearance` | string (JSON) | Serialised appearance settings |

**Response (success):**

```json
{
    "success": true,
    "data": { "message": "Settings saved successfully." }
}
```

---

### `moderncart_fetch_whats_new`

Proxies the changelog RSS feed from `cartflows.com` to avoid CORS issues.

**Method:** `GET` (admin only)
**Nonce:** `moderncart_fetch_whats_new` (query param: `nonce`)
**Capability:** `manage_options`

**Response:** RSS XML body from `https://cartflows.com/product/modern-cart/feed/`

---

### `moderncart_complete_onboarding`

Processes the onboarding wizard submission.

**Method:** `GET` (admin only, body via `php://input`)
**Nonce:** `moderncart_onboarding_nonce` (query param: `nonce`)
**Capability:** `manage_options`

**Request Body (JSON array):**

```json
[
    null,
    { "cart_type": "slideout", "floating_cart_button_position": "bottom-right", "enable_free_shipping_bar": true, "enable_product_recommendations": false },
    { "user_detail_firstname": "Jane", "user_detail_lastname": "Doe", "user_detail_email": "jane@example.com", "optin_newsletter_updates": true, "optin_usage_tracking": false },
    { "cartflows": true, "woo-cart-abandonment-recovery": true, "sureforms": false, "surerank": false }
]
```

**Response (success):**

```json
{ "success": true }
```

**Notes:**
- Index 0: ignored
- Index 1: cart style preferences → mapped to option keys and saved
- Index 2: user details → POSTed to OttoKit webhook
- Index 3: plugin slugs with `true` value → installed and activated via `Helper::install_wordpress_plugins()`
- Sets `moderncart_is_onboarding_complete = 'yes'` on completion

---

## Localised AJAX Object

The frontend JavaScript receives cart configuration via `wp_localize_script` as `moderncart_ajax_object`:

```javascript
moderncart_ajax_object = {
    ajax_url: "https://example.com/wp-admin/admin-ajax.php",
    ajax_nonce: "<nonce_value>",
    general_error: "Somethings wrong! try again later",
    edit_cart_text: "Edit Cart",
    is_needed_edit_cart: true, // false on CartFlows steps with Astra
    empty_cart_recommendation: "disabled",
    animation_speed: "300",
    enable_coupon_field: "minimize",
    cart_redirect_after_add: false, // or URL string
    cart_opening_direction: "right",
    disable_ajax_add_to_cart: false
}
```

Filterable via `moderncart_localize_script_args`.
