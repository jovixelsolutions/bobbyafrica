# Testing Guide

CartFlows uses two testing frameworks: **PHPUnit** for PHP unit/integration tests and **Playwright** for E2E browser tests, with a **wp-env** Docker environment for local test WordPress instances.

## PHP Unit Tests

### Stack

| Tool | Version | Purpose |
|------|---------|---------|
| PHPUnit | ^8 | Test runner |
| Yoast PHPUnit Polyfills | Latest | PHPUnit 8–10 compatibility shims |
| PHPStan | ^1.9 | Static analysis |
| szepeviktor/phpstan-wordpress | ^1.1 | WordPress-specific PHPStan stubs |

### Running Tests

```bash
# Run all PHP unit tests
composer test

# Run directly with PHPUnit
vendor/bin/phpunit

# Run a specific test class
vendor/bin/phpunit --filter TestClassName

# Run a specific test method
vendor/bin/phpunit --filter TestClassName::test_method_name

# Run tests in a specific group
vendor/bin/phpunit --group group-name
```

### Test Location

```
tests/php/
├── stubs/
│   └── cf-stubs.php    ← PHPStan stubs for CartFlows classes
└── *.php               ← PHPUnit test cases
```

### PHPStan Stubs

PHPStan stubs live at `tests/php/stubs/cf-stubs.php`. These provide type information for CartFlows classes that PHPStan cannot infer.

Regenerate stubs when plugin classes change significantly:

```bash
composer update-stubs
```

### Static Analysis

```bash
# Run PHPStan static analysis
composer phpstan
```

PHPStan configuration is in `phpstan.neon` at the project root. The level and custom rules are defined there.

### PHPCS Linting

```bash
# Check WordPress coding standards
composer lint

# Auto-fix PHPCS violations
composer format
```

---

## E2E Tests (Playwright)

### Stack

| Tool | Version | Purpose |
|------|---------|---------|
| Playwright | Latest | Browser automation + assertions |
| wp-env | ^4.2.0 | Docker-based WordPress environment |
| `@wordpress/env` | ^4.2.0 | wp-env npm package |

### Running E2E Tests

```bash
# Headless (CI) mode
npm run test:e2e

# Interactive / headed mode (watch browser)
npm run test:e2e:interactive

# Playwright test runner
npm run play:run

# Playwright interactive mode
npm run play:run:interactive
```

### Test Location

```
tests/e2e/
└── *.spec.js    ← Playwright test files
```

### Writing E2E Tests

E2E tests follow Playwright's page object model. Tests interact with the CartFlows admin UI and frontend checkout flows through a real (Docker) WordPress environment.

Example structure:

```js
import { test, expect } from '@playwright/test';

test( 'user can create a flow', async ( { page } ) => {
    await page.goto( '/wp-admin/admin.php?page=cartflows' );
    await page.click( '[data-testid="create-flow"]' );
    await expect( page.locator( '.wcf-flow-title' ) ).toBeVisible();
} );
```

---

## wp-env (Local Docker Environment)

`wp-env` provides an isolated WordPress environment for both running E2E tests and local development.

### Commands

```bash
# Start WordPress Docker environment
npm run env:start

# Stop environment
npm run env:stop

# Clean environment (reset database, keep images)
npm run env:clean

# Destroy environment completely (remove containers + volumes)
npm run env:destroy
```

### Configuration

wp-env is configured in `.wp-env.json` at the project root (or parent directory). Typical configuration includes:

```json
{
    "core": "WordPress/WordPress#trunk",
    "plugins": [ "." ],
    "themes": [],
    "port": 8888,
    "testsPort": 8889
}
```

The test environment runs on a separate port (8889) from the development environment (8888).

---

## Playwright Configuration

Playwright configuration is typically in `playwright.config.js` at the project root. It specifies:
- Base URL pointing to the wp-env test instance
- Browser targets (Chromium, Firefox, WebKit)
- Screenshot and video settings on failure

---

## CI / Automated Testing

Tests are run in CI against the same wp-env Docker setup. Ensure the environment is running before executing E2E tests.

### Recommended Test Order

1. Start wp-env: `npm run env:start`
2. Run PHP unit tests: `composer test`
3. Run PHP static analysis: `composer phpstan`
4. Run E2E tests: `npm run test:e2e`
5. Stop wp-env: `npm run env:stop`

---

## Code Quality Tools

### JavaScript

```bash
npm run lint-js          # ESLint
npm run lint-js:fix      # ESLint + auto-fix
npm run lint-css         # Stylelint
npm run lint-css:fix     # Stylelint + auto-fix
npm run pretty           # Prettier check
npm run pretty:fix       # Prettier auto-fix
```

### PHP

```bash
composer lint            # PHPCS
composer format          # phpcbf auto-fix
composer phpstan         # PHPStan
```

---

## Test Exclusions

PHPCS is configured to exclude these directories from scanning:

- `admin-core/assets/` (compiled JS/CSS)
- `vendor/` (Composer dependencies)
- `node_modules/` (npm packages)
- `libraries/` (vendored BSF packages)
- `tests/php/` (test files — different standards apply)

---

## Related Pages

- [Contributing-Guide](Contributing-Guide)
- [Environment-Configuration](Environment-Configuration)
- [WordPress-Coding-Standards](WordPress-Coding-Standards)
- [Deployment-Guide](Deployment-Guide)
