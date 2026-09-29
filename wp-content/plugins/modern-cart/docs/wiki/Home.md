# Modern Cart Starter for WooCommerce — Developer Wiki

**Plugin:** Modern Cart Starter for WooCommerce
**Package:** `brainstormforce/modern-cart-woo`
**Version:** 1.0.7
**Author:** CartFlows / BrainstormForce
**License:** GPLv2 or later
**Requires:** WordPress 5.4+, PHP 7.4+, WooCommerce 3.0+
**Tested up to:** WordPress 6.9, WooCommerce 9.8.4

---

## What Is Modern Cart?

Modern Cart replaces the default WooCommerce cart experience with a slide-out drawer, floating cart button, in-cart coupon form, free shipping progress bar, and product recommendations — all rendered via PHP templates and updated via AJAX without page reloads.

It is built as a WordPress plugin using:
- PHP with PSR-4-style autoloading (custom SPL autoloader)
- React 18 + Redux admin settings UI
- Tailwind CSS 3.x for admin styles
- `@wordpress/scripts` build tooling
- Grunt for minification, RTL, and zip bundling

---

## Quick Navigation

| Topic | Page |
|-------|------|
| Installation & first setup | [Getting-Started](Getting-Started) |
| Plugin boot sequence & class map | [Architecture-Overview](Architecture-Overview) |
| All `MODERNCART_*` constants | [Plugin-Constants](Plugin-Constants) |
| WordPress hooks & filters | [WordPress-Hooks](WordPress-Hooks) |
| Settings option groups & defaults | [Settings-Reference](Settings-Reference) |
| Cart features (slide-out, floating, etc.) | [Cart-Features](Cart-Features) |
| AJAX endpoints reference | [AJAX-Endpoints](AJAX-Endpoints) |
| PHP template system & overrides | [Template-System](Template-System) |
| Admin settings UI & onboarding | [Admin-Settings-UI](Admin-Settings-UI) |
| Pro plugin version gate | [Pro-Plugin-Integration](Pro-Plugin-Integration) |
| Astra theme compatibility | [Theme-Compatibility](Theme-Compatibility) |
| Running tests | [Testing-Guide](Testing-Guide) |
| Build commands & coding standards | [Contributing-Guide](Contributing-Guide) |
| Common issues & fixes | [Troubleshooting-FAQ](Troubleshooting-FAQ) |
| Version history | [Changelog](Changelog) |

---

## Repository Layout

```
modern-cart/
├── modern-cart.php        # Entry point — constants, Pro version gate
├── plugin-loader.php      # Plugin_Loader singleton (autoloader + hooks)
├── inc/                   # Feature classes (namespace: ModernCart\Inc)
│   ├── cart.php           # Base Cart class — totals, helpers
│   ├── slide-out.php      # Slide_Out — renders drawer
│   ├── slide-out-ajax.php # Slide_Out_Ajax — AJAX handlers
│   ├── floating.php       # Floating — floating cart button
│   ├── floating-ajax.php  # Floating_Ajax — refresh AJAX
│   ├── helper.php         # Helper — settings, colors, utilities
│   ├── scripts.php        # Scripts — enqueue JS/CSS + dynamic styles
│   ├── functions.php      # Global template helper functions
│   └── traits/
│       └── get-instance.php  # Get_Instance trait (singleton)
├── admin-core/            # React admin UI (namespace: ModernCart\Admin_Core)
│   ├── admin-menu.php     # Admin_Menu — menu, scripts, AJAX settings save
│   └── inc/
│       └── settings-fields.php  # Settings_Fields — field definitions
├── templates/             # PHP render templates
│   ├── cart/              # Cart drawer partials
│   └── shop/              # Floating button & slide-out wrappers
├── assets/                # Compiled frontend JS/CSS (do not edit)
├── admin-core/assets/     # Compiled admin JS/CSS (do not edit)
├── languages/             # .pot, .po, .mo, .json translation files
├── tests/php/             # PHPUnit tests
└── vendor/                # Composer dependencies (committed)
```

---

## Key Design Principles

- **Singleton pattern** — every feature class exposes `get_instance()` via the `Get_Instance` trait
- **WooCommerce-gated** — all feature classes are only instantiated after confirming `class_exists('woocommerce')`
- **Pro version gate** — plugin halts gracefully if `modern-cart-woo` Pro is installed but < 1.2.0
- **Template overrides** — themes can override any template by placing files in `{theme}/modern-cart/`
- **Filter-first** — most output and behaviour is filterable via `moderncart_*` hooks

---

## External Services

| Service | URL | Purpose |
|---------|-----|---------|
| Changelog feed | `https://cartflows.com/product/modern-cart/feed/` | "What's New" panel in admin |
| Knowledge base | `https://cartflows.com/wp-json/powerful-docs/v1/get-docs` | Docs panel in admin |
| Onboarding webhook | `MODERNCART_ONBOARDING_USER_SUB_WORKFLOW_URL` | Submits user details on onboarding completion |

No user data is sent to the changelog or knowledge base endpoints. The onboarding webhook only receives data explicitly provided by the site admin during the onboarding wizard.
