# WordPress Coding Standards

CartFlows enforces strict coding standards across PHP and JavaScript using automated tools integrated into the development workflow.

## PHP Standards

### PHPCS + WordPress Coding Standards

CartFlows uses **PHP_CodeSniffer** (PHPCS) with the **WordPress Coding Standards** ruleset plus **VIP-Go** rules.

**Configuration file:** `phpcs.xml.dist`

```bash
# Check coding standards
composer lint

# Auto-fix fixable violations
composer format
```

### Ruleset (`phpcs.xml.dist`)

The ruleset includes:

- **WordPress** — Core WordPress PHP standards
- **WordPress-Extra** — Additional WordPress best practices
- **WordPress-Docs** — Documentation requirements
- **VIP-Go** — Automattic VIP Go platform standards

### Custom Sanitizer Registration

`wc_clean` is registered as a recognized sanitizer function in `phpcs.xml.dist`. PHPCS will not flag it as an unrecognized sanitizer:

```xml
<rule ref="WordPress.Security.ValidatedSanitizedInput">
    <properties>
        <property name="customSanitizingFunctions" type="array">
            <element value="wc_clean"/>
        </property>
    </properties>
</rule>
```

Use `wc_clean()` for WooCommerce data sanitization (it wraps `wp_kses_post` for arrays, `sanitize_text_field` for strings).

### Excluded Paths

These directories are excluded from PHPCS scanning:

```xml
<exclude-pattern>admin-core/assets/</exclude-pattern>
<exclude-pattern>vendor/</exclude-pattern>
<exclude-pattern>node_modules/</exclude-pattern>
<exclude-pattern>libraries/</exclude-pattern>
<exclude-pattern>tests/php/</exclude-pattern>
```

### Key Rules Applied

| Category | Rule | Pattern |
|----------|------|---------|
| Input sanitization | Always sanitize `$_POST`, `$_GET` | `sanitize_text_field()`, `wc_clean()`, `absint()` |
| Output escaping | Always escape output | `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()` |
| Nonce verification | Verify before processing | `wp_verify_nonce()` / `check_ajax_referer()` |
| Capability checks | Before mutations | `current_user_can( 'manage_options' )` |
| File access protection | Top of every file | `if ( ! defined( 'ABSPATH' ) ) { exit; }` |

---

## PHPStan Static Analysis

**Configuration file:** `phpstan.neon`

```bash
# Run static analysis
composer phpstan
```

### Stubs

PHPStan uses custom stubs for CartFlows and WordPress:

| Stub Package | Purpose |
|-------------|---------|
| `szepeviktor/phpstan-wordpress` | WordPress function stubs |
| `php-stubs/wordpress-stubs` | WordPress core stubs |
| `php-stubs/woocommerce-stubs` | WooCommerce stubs |
| `tests/php/stubs/cf-stubs.php` | CartFlows-specific stubs |

Regenerate CartFlows stubs after significant class changes:
```bash
composer update-stubs
```

---

## JavaScript Standards

### ESLint

**Configuration:** `@wordpress/eslint-plugin` (WordPress standard ESLint config)

```bash
npm run lint-js         # Check JS/JSX files
npm run lint-js:fix     # Auto-fix violations
```

Key rules enforced:
- No `console.log` in production code
- Consistent React hook usage (rules-of-hooks)
- Import ordering
- No unused variables

### Prettier

**Package:** `wp-prettier` (WordPress-flavoured Prettier)

```bash
npm run pretty          # Check formatting
npm run pretty:fix      # Auto-fix formatting
```

Prettier handles:
- Consistent quote style (single quotes)
- Trailing commas
- Line length
- Bracket spacing

### Stylelint (CSS)

```bash
npm run lint-css        # Check SCSS/CSS files
npm run lint-css:fix    # Auto-fix violations
```

---

## Commit Message Standards

CartFlows uses the **Conventional Commits** format:

```
<type>: <short description>

[optional body]
```

### Types

| Type | Usage |
|------|-------|
| `feat:` | New feature |
| `fix:` | Bug fix |
| `docs:` | Documentation changes |
| `chore:` | Build, tooling, or dependency updates |
| `refactor:` | Code refactoring (no feature/bug change) |
| `test:` | Adding or updating tests |
| `style:` | Formatting, whitespace (no logic changes) |

### Examples

```
feat: add order bump preview to step editor
fix: checkout fields not saving on mobile layout
docs: update REST API reference for flows endpoint
chore: bump @wordpress/scripts to 19.2.2
refactor: extract checkout field renderer to separate class
```

---

## Pre-commit Hook

Running `npm install` installs a git pre-commit hook. It runs before each commit to catch violations early.

**Do not** bypass with `--no-verify` unless explicitly approved by the team.

---

## File Header Standards

Every PHP file must start with:

```php
<?php
/**
 * Brief description of the file.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```

---

## IDE Integration

For VS Code, install:
- **PHP Intelephense** — PHP language server
- **ESLint** — JS linting
- **Prettier** — Code formatting
- **Stylelint** — CSS linting
- **PHP CS Fixer** — PHP formatting

Configure VS Code to use the project's `.eslintrc` and run Prettier on save.

---

## Related Pages

- [Contributing-Guide](Contributing-Guide)
- [Testing-Guide](Testing-Guide)
- [WordPress-Plugin-Structure](WordPress-Plugin-Structure)
- [Build-System](Build-System)
