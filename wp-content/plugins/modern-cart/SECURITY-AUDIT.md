# Security Audit Report — Modern Cart Plugin

**Date:** 2026-02-26
**Branch:** dev
**Plugin Version:** 1.0.7
**Total Findings:** 8 (1 Critical, 3 High, 3 Medium, 1 Low)
**Passes Basic Security Review:** No

---

## Critical (1)

### 1. `extract()` on filterable data enables variable injection / LFI

- **File:** `inc/functions.php:37`
- **Description:** `extract($args)` overwrites local variables including `$template`, which is later used in `require $template`. A malicious filter on `moderncart_get_template_part_args` could include any file on the server.
- **Vulnerable Code:**
  ```php
  $args = apply_filters(
      'moderncart_get_template_part_args',
      wp_parse_args( $args, $defaults ),
      compact( 'slug', 'name' )
  );

  if ( $args && is_array( $args ) ) {
      extract( $args ); // Overwrites $template, $slug, $name
  }
  // ...
  require $template; // Includes whatever file $template now points to
  ```
- **Fix:** Replace `extract($args)` with explicit variable assignments (e.g., `$slug = $args['slug']`).

---

## High (3)

### 2. Unsanitized RSS feed body echoed to admin

- **File:** `admin-core/admin-menu.php:350`
- **Description:** The `fetch_whats_new()` AJAX handler fetches an RSS feed from `cartflows.com` and echoes the raw response body without any escaping. A compromised feed or MITM attack injects arbitrary HTML/JavaScript into the admin dashboard.
- **Vulnerable Code:**
  ```php
  echo wp_remote_retrieve_body( wp_remote_get( 'https://cartflows.com/product/modern-cart/feed/' ) );
  exit;
  ```
- **Fix:** Parse the RSS with `fetch_feed()` or apply `wp_kses_post()` before echoing.

### 3. Plugin install accepts arbitrary slugs without allowlist

- **File:** `admin-core/admin-menu.php:404`
- **Description:** Plugin slugs from user-submitted JSON (index `3`) are passed to `Helper::install_wordpress_plugins()` without validation against a known allowlist. A compromised admin session could install and activate any plugin from WordPress.org.
- **Vulnerable Code:**
  ```php
  if ( 3 === $index && (bool) $value ) {
      $installable_plugin_slugs[] = $key; // NOT validated against allowlist
  }
  Helper::install_wordpress_plugins( $installable_plugin_slugs );
  ```
- **Fix:** Validate slugs against a hardcoded allowlist before passing to `install_wordpress_plugins()`.

### 4. jQuery `.html()` injects AJAX response without client-side sanitization

- **File:** `assets/js/cart.js` — lines 851, 893, 951, 1281, 1326
- **Description:** Multiple AJAX callbacks inject `response.content` directly into the DOM via `.html()`. While server-side templates use escaping, the client performs zero validation of the response origin or content integrity.
- **Vulnerable Code:**
  ```javascript
  $( '#moderncart-slide-out-modal' ).html( response.content );
  ```
- **Fix:** Use `.text()` where HTML is not needed, or validate response structure before `.html()` injection.

---

## Medium (3)

### 5. WP_Error message used without escaping at assignment

- **File:** `inc/slide-out-ajax.php:174`
- **Description:** `$valid->get_error_message()` is assigned to `$message` unescaped. Third-party coupon validation filters could return unexpected HTML.
- **Vulnerable Code:**
  ```php
  $message = $valid->get_error_message(); // Unescaped WP_Error message
  ```
- **Fix:** Wrap with `esc_html()` at assignment: `$message = esc_html( $valid->get_error_message() );`

### 6. `dangerouslySetInnerHTML` for filterable SVG data

- **File:** `admin-core/assets/src/components/fields/CartPreview.js:100`
- **Description:** SVG icon markup is rendered via `dangerouslySetInnerHTML`. The data originates from `Helper::get_cart_icons()` (hardcoded SVGs) but passes through the `moderncart_settings_admin_localize_script` filter, which could be hooked by malicious code.
- **Vulnerable Code:**
  ```jsx
  <div dangerouslySetInnerHTML={ { __html: iconSvg } } />
  ```
- **Fix:** Sanitize SVG through a whitelist or render as a React component instead.

### 7. Cookie value compared without `sanitize_text_field()`

- **File:** `inc/helper.php:682`
- **Description:** `$_COOKIE['woo-share']` is read and compared directly to a database option without sanitization.
- **Vulnerable Code:**
  ```php
  if ( isset( $_COOKIE['woo-share'] ) && get_option( 'woocommerce_share_key' ) === $_COOKIE['woo-share'] ) {
  ```
- **Fix:** Sanitize cookie: `sanitize_text_field( wp_unslash( $_COOKIE['woo-share'] ) )`

---

## Low (1)

### 8. Silent return on nonce failure

- **File:** `inc/floating-ajax.php:41`
- **Description:** When nonce verification fails, the handler returns silently instead of sending a proper error response.
- **Vulnerable Code:**
  ```php
  if ( ! isset( $_POST['moderncart_nonce'] ) || ! wp_verify_nonce( ... ) ) {
      return; // Should use wp_die() for consistency
  }
  ```
- **Fix:** Replace `return;` with `wp_send_json_error( 'Nonce verification failed.', 403 );`

---

## Summary

| Metric | Value |
|--------|-------|
| Files scanned | ~30+ PHP, React, and JS files across 6 directories |
| Critical | 1 |
| High | 3 |
| Medium | 3 |
| Low | 1 |
| **Total findings** | **8** |

## Top 3 Priority Actions

1. **Replace `extract()` with explicit assignments** in `inc/functions.php:37` — highest risk (LFI)
2. **Sanitize RSS feed output** in `admin-core/admin-menu.php:350` — stored XSS via compromised feed
3. **Add plugin slug allowlist** in `admin-core/admin-menu.php:404` — prevents arbitrary plugin installation
