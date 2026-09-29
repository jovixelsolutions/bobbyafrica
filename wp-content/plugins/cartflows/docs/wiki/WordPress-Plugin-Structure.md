# WordPress Plugin Structure

CartFlows follows WordPress coding conventions with PSR-4 PHP namespacing in its admin layer and strict coding standards enforced by PHPCS.

## File Naming Conventions

| Type | Pattern | Example |
|------|---------|---------|
| Class files | `class-cartflows-{name}.php` | `class-cartflows-loader.php` |
| Include files | `{name}.php` | `admin-loader.php` |
| Template files | `{name}.php` | `checkout-form.php` |
| Interface files | `class-cartflows-{name}-interface.php` | `class-cartflows-logger-interface.php` |

## Prefixes

All CartFlows identifiers use consistent prefixes to avoid conflicts with other plugins:

| Identifier type | Prefix | Example |
|-----------------|--------|---------|
| Hook names (actions/filters) | `cartflows_` | `cartflows_before_checkout_form` |
| Option names | `cartflows_` | `cartflows_global_settings` |
| Post meta keys | `wcf-` | `wcf-checkout-layout` |
| AJAX actions | `cartflows_` | `wp_ajax_cartflows_flows` |
| CSS classes | `wcf-` | `wcf-checkout-wrap` |
| JS global objects | `wcf` / `cartflows` | `wcfEditorAppData` |

## Capabilities

Admin operations require the `manage_options` capability. This is the standard WordPress administrator capability used for all CartFlows settings and flow management operations.

```php
// Example permission check pattern used in REST API controllers
'permission_callback' => function() {
    return current_user_can( 'manage_options' );
},
```

## PHP Namespaces

The admin layer uses PSR-4 namespacing:

```
CartflowsAdmin\AdminCore\Api    →  admin-core/api/
CartflowsAdmin\AdminCore\Ajax   →  admin-core/ajax/
```

Core plugin classes (`classes/`) follow WordPress global class naming conventions without namespaces.

## Singleton Pattern

The main bootstrap class uses the singleton pattern:

```php
// In class-cartflows-loader.php
public static function get_instance() {
    if ( ! isset( self::$instance ) ) {
        self::$instance = new self();
    }
    return self::$instance;
}
```

Called from `cartflows.php`:
```php
Cartflows_Loader::get_instance();
```

## Custom Post Types

CartFlows registers two custom post types:

| CPT Slug | Label | Purpose |
|----------|-------|---------|
| `wcf-flow` | Flow / Funnel | A complete sales funnel (container) |
| `wcf-step` | Step | An individual page within a funnel |

Steps are linked to flows via post meta. The flow editor (React) renders a visual canvas showing all steps in a flow.

## WordPress Hooks Integration

CartFlows hooks into WordPress and WooCommerce at several points:

- **`plugins_loaded`** — Main plugin bootstrap
- **`init`** — CPT registration, shortcodes
- **`wp_enqueue_scripts`** — Frontend assets
- **`admin_enqueue_scripts`** — Admin React app assets
- **`woocommerce_checkout_*`** — Checkout customisations
- **`rest_api_init`** — REST endpoint registration

## Security Conventions

Every user-facing operation follows the WordPress security trifecta:

1. **Nonce verification** — All AJAX handlers verify nonces via `AjaxBase`
2. **Capability checks** — `current_user_can( 'manage_options' )` before mutations
3. **Input sanitisation** — `sanitize_text_field()`, `wc_clean()`, `absint()`
4. **Output escaping** — `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`

Note: `wc_clean()` is registered as a custom sanitizer in `phpcs.xml.dist` so PHPCS recognises it.

## Template Override System

WooCommerce templates are overridden by placing files in `woocommerce/`:

```
woocommerce/
├── checkout/
│   ├── form-checkout.php
│   └── review-order.php
└── ...
```

This follows standard WooCommerce template override conventions.

## WordPress Options Storage

Plugin-wide settings are stored in `wp_options`:

| Option | Purpose |
|--------|---------|
| `cartflows_global_settings` | Global plugin configuration |
| `cartflows_version` | Installed plugin version (used for upgrade routines) |

## Autoloading

PHP classes in `admin-core/` are autoloaded via Composer's PSR-4 autoloader configured in `composer.json`. Core classes in `classes/` are manually required by the loader.

## Related Pages

- [Architecture-Overview](Architecture-Overview)
- [REST-API-Reference](REST-API-Reference)
- [AJAX-API-Reference](AJAX-API-Reference)
- [Database-Schema](Database-Schema)
- [WordPress-Coding-Standards](WordPress-Coding-Standards)
