# Getting Started

This page covers installation, activation, the onboarding wizard, and the first-run experience for the Modern Cart plugin.

---

## Requirements

| Requirement | Minimum |
|-------------|---------|
| WordPress | 5.4 |
| PHP | 7.4 |
| WooCommerce | 3.0 |
| Modern Cart Pro (if used) | 1.2.0 |

---

## Installation

### Via WordPress Plugin Directory
1. Go to **Plugins → Add New** in wp-admin
2. Search for "Modern Cart"
3. Click **Install Now**, then **Activate**

### Via ZIP Upload
1. Download `modern-cart.zip`
2. Go to **Plugins → Add New → Upload Plugin**
3. Upload the ZIP and activate

### Via WP-CLI
```bash
wp plugin install modern-cart --activate
```

---

## First Activation — Onboarding Redirect

On first activation, `Plugin_Loader::activate()` sets a transient:

```php
set_transient( 'moderncart_redirect_to_onboarding', 'yes' );
```

On the next `admin_init`, `redirect_to_onboarding()` fires and redirects to:

```
/wp-admin/admin.php?page=moderncart_settings&nonce=<nonce>&onboarding=1
```

The admin UI detects the `onboarding=1` parameter (verified by nonce) and renders the onboarding wizard fullscreen (hides the WordPress toolbar and sidebar via inline CSS).

The onboarding wizard collects:

| Step | Data |
|------|------|
| Step 1 | Cart type, floating button position, free shipping bar toggle, product recommendations toggle |
| Step 2 | User first name, last name, email, newsletter opt-in, usage tracking opt-in |
| Step 3 | Optional companion plugins to install (CartFlows, Cart Abandonment Recovery, SureForms, SureRank) |

On completion, `moderncart_complete_onboarding` AJAX handler:
1. Saves all settings to `wp_options`
2. POSTs user details to the OttoKit webhook
3. Installs and activates any selected companion plugins
4. Sets `moderncart_is_onboarding_complete = 'yes'` — prevents future redirects

### Skipping Onboarding
The onboarding redirect only runs once. If `moderncart_is_onboarding_complete` is already `yes`, activation does nothing extra.

---

## Accessing Settings After Setup

Navigate to: **WooCommerce → Modern Cart** (`/wp-admin/admin.php?page=moderncart_settings`)

The settings UI is a React Single Page Application rendered into `<div id="moderncart-settings">`.

---

## Verifying the Plugin Is Active

The plugin only loads its features if WooCommerce is active. You can verify this with:

```php
// Check if Modern Cart is running
if ( defined( 'MODERNCART_VER' ) ) {
    // Plugin is loaded
}

// Check if WooCommerce was found
if ( class_exists( 'woocommerce' ) && defined( 'MODERNCART_VER' ) ) {
    // All cart features are active
}
```

---

## Pro Plugin Setup

If you have **Modern Cart Pro** (`modern-cart-woo`):
1. Install and activate the Pro plugin first
2. Ensure Pro version is **1.2.0 or higher**
3. Activate the Starter plugin

If Pro is installed but older than 1.2.0, an admin notice appears and the Starter plugin halts initialisation. See [Pro-Plugin-Integration](Pro-Plugin-Integration).

---

## Development Environment Setup

```bash
# Clone and enter the plugin directory
cd app/public/wp-content/plugins/modern-cart/

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Start JS watch mode (development)
npm run start

# Build production assets
npm run build
```

See [Contributing-Guide](Contributing-Guide) for full build documentation.
