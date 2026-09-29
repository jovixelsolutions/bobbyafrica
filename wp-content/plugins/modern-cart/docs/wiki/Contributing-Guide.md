# Contributing Guide

This guide covers the development workflow, build commands, coding standards, and commit conventions for the Modern Cart plugin.

---

## Prerequisites

| Tool | Required Version | Notes |
|------|-----------------|-------|
| PHP | 7.4+ | Minimum runtime requirement |
| Composer | 2.x | PHP dependency management |
| Node.js | LTS | JS build tooling |
| npm | 8+ | Package management |
| WP-CLI | Any | For i18n and env commands |
| WordPress | 5.4+ | Dev environment |
| WooCommerce | 3.0+ | Required plugin |

---

## Initial Setup

```bash
cd app/public/wp-content/plugins/modern-cart/

# PHP dependencies
composer install

# JS dependencies
npm install
```

---

## JavaScript / CSS Build Commands

| Command | Description |
|---------|-------------|
| `npm run build` | Production build via `wp-scripts build` |
| `npm run start` | Watch mode for development (live rebuilds) |
| `npm run lint-js` | ESLint check (via `wp-scripts lint-js`) |
| `npm run lint-js:fix` | Prettier + ESLint auto-fix |
| `npm run lint-css` | Stylelint check (via `wp-scripts lint-style`) |
| `npm run lint-css:fix` | Auto-fix CSS lint issues |
| `npm run pretty` | Check Prettier formatting |
| `npm run pretty:fix` | Auto-fix Prettier formatting |

### Build Entry Points

`@wordpress/scripts` reads `webpack.config.js` which extends the default `wp-scripts` config. The main entry points are in `admin-core/` (for the React settings app).

Built assets output to:
- `admin-core/assets/build/` — Admin JS and CSS
- `assets/` — Frontend JS and CSS (compiled separately)

**Never edit files in `assets/` or `admin-core/assets/build/` directly.** These are build outputs.

### Tailwind CSS

Tailwind CSS 3.x is configured in `tailwind.config.js`. PostCSS configuration is in `postcss.config.js`. The `@tailwindcss/forms` and `@tailwindcss/typography` plugins are included.

---

## PHP Build Commands

| Command | Description |
|---------|-------------|
| `composer lint` | Run PHPCS (code standards check) |
| `composer format` | Run PHPCBF (auto-fix PHPCS violations) |
| `composer phpstan` | Run PHPStan static analysis (2GB memory limit) |
| `composer test` | Run PHPUnit tests |
| `composer insights` | Run PHPInsights code quality analysis |
| `composer insights:fix` | Run PHPInsights with auto-fix |
| `composer gen-stubs` | Generate PHPStan stubs from plugin artifacts |
| `composer gen-baseline` | Regenerate PHPStan baseline |

---

## Grunt Commands

Grunt handles minification, RTL CSS generation, text domain extraction, and ZIP packaging:

```bash
# Run default grunt tasks (minify, RTL, etc.)
grunt

# Available grunt tasks (see Gruntfile.js for all):
grunt cssmin    # Minify CSS
grunt uglify    # Minify JS
grunt rtlcss    # Generate RTL CSS variants
grunt clean     # Clean build artifacts
grunt compress  # Create ZIP package
grunt copy      # Copy files to build directory
```

---

## i18n Commands

```bash
npm run i18n          # Generate .pot file (with grunt textdomain extraction first)
npm run i18n:po       # Update .po files from .pot
npm run i18n:mo       # Compile .mo binaries
npm run i18n:json     # Generate JSON translation files for JavaScript

# AI translation (requires gpt-po)
npm run i18n:gptpo:nl   # Dutch
npm run i18n:gptpo:fr   # French
npm run i18n:gptpo:de   # German
npm run i18n:gptpo:es   # Spanish
npm run i18n:gptpo:it   # Italian
npm run i18n:gptpo:pt   # Portuguese
npm run i18n:gptpo:pl   # Polish
```

---

## Coding Standards

### PHP Standards

Follow **WordPress Coding Standards (WPCS)**. Rulesets enforced in `phpcs.xml`:
- `WordPress-Core`
- `WordPress-Docs`
- `WordPress-Extra`
- `VIP-Go`
- `PHPCompatibility`

**Always run `composer format` before committing PHP changes.**

#### Security Checklist

- [ ] Sanitize all input: `sanitize_text_field()`, `absint()`, `sanitize_hex_color()`, `wp_unslash()`
- [ ] Escape all output: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`
- [ ] Verify nonces before processing form/AJAX data: `wp_verify_nonce()` or `check_ajax_referer()`
- [ ] Check capabilities before privileged actions: `current_user_can()`
- [ ] Use `wp_safe_redirect()` not `wp_redirect()`
- [ ] Prefix all global functions, hooks, option keys with `moderncart_` or `MODERNCART_`

#### Namespace Convention

All plugin classes must be in the `ModernCart\` namespace:
- Feature classes: `ModernCart\Inc\ClassName`
- Admin classes: `ModernCart\Admin_Core\ClassName`

Files map to classes via the autoloader (camelCase → kebab-case):
- `ModernCart\Inc\SlideOut` → `inc/slide-out.php`
- `ModernCart\Admin_Core\AdminMenu` → `admin-core/admin-menu.php`

#### Class Patterns

- All feature classes use the `Get_Instance` trait (singleton)
- All classes use `@since` tags in docblocks
- Return types should be declared where possible (PHP 7.4+)
- PHPStan must pass — do not add `@phpstan-ignore` for new code

### JavaScript Standards

- React 18.x functional components with hooks
- Redux for state management in the admin
- ESLint rules from `@wordpress/eslint-plugin`
- Prettier formatting from `@wordpress/prettier-config`
- All translatable strings use `@wordpress/i18n` (`__()`, `_n()`, `_x()`)

**Always run `npm run lint-js:fix` before committing JS changes.**

### CSS Standards

- Tailwind CSS utility classes in JSX/TSX
- Custom component CSS in PostCSS
- RTL support via `grunt-rtlcss`
- CSS class naming: `moderncart-` prefix for all plugin classes

---

## Text Domain

All translatable strings must use the `modern-cart` text domain:

```php
__( 'String', 'modern-cart' )
esc_html__( 'String', 'modern-cart' )
_n( 'singular', 'plural', $count, 'modern-cart' )
_x( 'String', 'context', 'modern-cart' )
```

```javascript
import { __ } from '@wordpress/i18n';
__( 'String', 'modern-cart' );
```

---

## Pre-Commit Checklist

Before every commit:

```bash
# PHP
composer format          # Auto-fix code style
composer lint            # Verify no PHPCS violations remain
composer phpstan         # Static analysis must pass

# JavaScript
npm run lint-js:fix      # Fix and verify JS
npm run lint-css:fix     # Fix and verify CSS

# Verify build
npm run build            # Production build must succeed
```

---

## Pull Request Guidelines

1. **Branch from `dev`** (or the current development branch)
2. **One feature/fix per PR** — keep changes focused
3. **Update the changelog** in `README.md` under the appropriate version
4. **Test the change manually** against the checklist in [Testing-Guide](Testing-Guide)
5. **No debug code** — no `console.log`, no `error_log`, no `var_dump` in PR code
6. **Settings changes** must update `Helper::get_defaults()` schema AND `Settings_Fields::get_fields()`
7. **New hooks** must follow naming convention: `moderncart_{description}` (actions) or `moderncart_{description}` (filters)
8. **New options** must be added to the sanitize schema in `Admin_Menu::sanitize_data()`

---

## Adding a New Setting

When adding a new setting option:

1. **Add to `Helper::get_defaults()`** — define key, default value, and type (string/boolean/number/hex)
2. **Add to `Settings_Fields::get_fields()`** — define UI field (label, type, options, doc link)
3. **Use in PHP** — read via `$this->get_option('new_key', MODERNCART_SETTINGS, $default)` or `Helper::get_option(OPTION_GROUP)['new_key']`
4. **Add to sanitizer** — the schema-based sanitizer in `Admin_Menu::sanitize_data()` picks up the type automatically from `get_defaults()`
5. **Test** — verify the setting saves and loads correctly

---

## Adding a New Template

1. Create the PHP file in `templates/cart/` or `templates/shop/`
2. Use `moderncart_get_template_part()` to load it (supports theme overrides automatically)
3. Document available `$data` variables in the template file docblock
4. Register it in the [Template-System](Template-System) documentation

---

## Vendor Directory

`vendor/` is committed to the repository for distribution (no Composer on production servers). After any `composer install` or `composer update`, commit the updated `vendor/` directory.
