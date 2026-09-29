# Environment Configuration

CartFlows uses `@wordpress/env` (wp-env) to provide an isolated Docker-based WordPress environment for local development and E2E testing.

## Prerequisites

| Tool | Purpose |
|------|---------|
| Docker Desktop | Runs the WordPress containers |
| Node.js + npm | Manages wp-env and JavaScript dependencies |
| Composer | Manages PHP dependencies |
| PHP 7.2+ | Required for running PHP unit tests locally |

## wp-env Setup

wp-env creates two separate WordPress environments in Docker:

| Environment | Port | Purpose |
|-------------|------|---------|
| Development | `http://localhost:8888` | Local dev environment |
| Tests | `http://localhost:8889` | E2E test environment |

### Start the environment

```bash
npm run env:start
```

This:
1. Pulls WordPress and MySQL Docker images
2. Starts the containers
3. Installs and activates the CartFlows plugin
4. Sets up the test environment on port 8889

### Stop the environment

```bash
npm run env:stop
```

### Reset to clean state

```bash
# Reset database but keep Docker images
npm run env:clean

# Destroy everything (containers + volumes)
npm run env:destroy
```

After `env:destroy`, you need to run `env:start` again to recreate the environment.

## wp-env Configuration

wp-env is configured via `.wp-env.json` in the project root (if present). A typical configuration for CartFlows:

```json
{
    "core": null,
    "plugins": [ "." ],
    "themes": [],
    "port": 8888,
    "testsPort": 8889,
    "config": {
        "WP_DEBUG": true,
        "WP_DEBUG_LOG": true,
        "SCRIPT_DEBUG": true
    }
}
```

| Config Key | Description |
|------------|-------------|
| `core` | WordPress version (`null` = latest) |
| `plugins` | `"."` includes CartFlows itself |
| `port` | Development environment port |
| `testsPort` | Test environment port |
| `WP_DEBUG` | Enable WordPress debug mode |
| `WP_DEBUG_LOG` | Log errors to `wp-content/debug.log` |
| `SCRIPT_DEBUG` | Use unminified scripts |

## Development Workflow

```bash
# 1. Install dependencies
composer install
npm install

# 2. Start WordPress environment
npm run env:start

# 3. Build React apps (watch mode for development)
npm run start

# 4. Open the development site
# → http://localhost:8888/wp-admin
# → Username: admin | Password: password (wp-env defaults)

# 5. Run tests
composer test
npm run test:e2e
```

## Manual WordPress Installation

If you prefer not to use wp-env, CartFlows can be developed in any WordPress environment:

1. Install WordPress locally (MAMP, LocalWP, Laragon, WP-CLI, etc.)
2. Install and activate WooCommerce
3. Clone CartFlows into `wp-content/plugins/cartflows/`
4. Run `composer install` in the plugin directory
5. Run `npm install && npm run build`
6. Activate the CartFlows plugin in WordPress admin

## Required WordPress Settings for Development

| Setting | Value | Reason |
|---------|-------|--------|
| `WP_DEBUG` | `true` | Show PHP errors |
| `WP_DEBUG_LOG` | `true` | Log to `debug.log` |
| `SCRIPT_DEBUG` | `true` | Use unminified scripts |
| Permalink structure | Post name | Required for CartFlows URLs |

Set these in `wp-config.php`:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'SCRIPT_DEBUG', true );
```

## Required Plugins

CartFlows requires WooCommerce. For full feature development, also install:
- WooCommerce (required)
- Any page builder you're developing for (Elementor, Beaver Builder, etc.)

## PHP Configuration

Recommended `php.ini` settings for development:

```ini
max_execution_time = 120
memory_limit = 256M
upload_max_filesize = 64M
post_max_size = 64M
```

## Assets in Development

For React development with hot-reloading:

```bash
npm run start   # Webpack watch mode — rebuilds on file change
```

This rebuilds `editor-app.js` and `settings-app.js` automatically when source files change. You must still reload the WordPress admin page to see changes.

**Note:** `npm run start` produces development (unminified) builds. Always run `npm run build` before committing.

## Environment Variables

CartFlows does not use `.env` files — all configuration is managed through WordPress's `wp-config.php` and plugin settings UI.

## Cleaning Up

```bash
# Remove build artifacts (not committed to git)
rm -rf admin-core/assets/build/*.js.map

# Remove PHP vendor directory (can be regenerated)
rm -rf vendor/
composer install

# Remove node_modules (can be regenerated)
rm -rf node_modules/
npm install
```

## Related Pages

- [Getting-Started](Getting-Started)
- [Testing-Guide](Testing-Guide)
- [Build-System](Build-System)
- [Contributing-Guide](Contributing-Guide)
