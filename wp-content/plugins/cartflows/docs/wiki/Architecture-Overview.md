# Architecture Overview

CartFlows is a WordPress plugin that creates checkout pages and sales funnels for WooCommerce. It follows a modular, singleton-bootstrapped architecture with a modern React frontend.

## Plugin Bootstrap

```
cartflows.php
  └── classes/class-cartflows-loader.php  (Cartflows_Loader singleton)
        ├── admin-loader.php              (admin-only components)
        ├── modules/                      (feature modules)
        └── compatibilities/             (third-party integrations)
```

The plugin entry point (`cartflows.php`) sets the `CARTFLOWS_FILE` constant and immediately requires the loader. `Cartflows_Loader` is a **singleton** — a single class instance bootstraps every plugin component, ensuring no duplicate initialisation.

Admin functionality is gated behind `is_admin()` and loaded via `admin-loader.php`, which boots the React-powered admin interface.

## High-Level Component Map

```
┌─────────────────────────────────────────────────────────────┐
│                       cartflows.php                         │
└──────────────────────┬──────────────────────────────────────┘
                       │
          ┌────────────▼────────────┐
          │  Cartflows_Loader       │  (singleton, classes/)
          │  register_activation_   │
          │  hook, init hooks       │
          └──┬────────────┬─────────┘
             │            │
   ┌─────────▼──┐    ┌────▼──────────────────────────────┐
   │  Frontend  │    │  Admin (admin-loader.php)          │
   │  classes/  │    │  ┌─────────────────────────────┐  │
   │  class-    │    │  │  REST API (admin-core/api/)  │  │
   │  cartflows-│    │  │  AJAX     (admin-core/ajax/) │  │
   │  frontend  │    │  │  Menus    (admin-core/inc/)  │  │
   └─────────┬──┘    │  │  React Apps (assets/build/) │  │
             │       │  └─────────────────────────────┘  │
             │       └───────────────────────────────────┘
             │
   ┌─────────▼──────────────────────────────────────────┐
   │  Feature Modules (modules/)                         │
   │  flow · checkout · optin · landing · thankyou      │
   │  gutenberg · elementor · beaver-builder · bricks   │
   │  email-report · woo-dynamic-flow                   │
   └────────────────────────────────────────────────────┘
```

## Key Directories

| Path | Purpose |
|------|---------|
| `cartflows.php` | Plugin entry point; sets `CARTFLOWS_FILE` constant |
| `classes/` | Core PHP classes — loader, admin, frontend, helper, utils |
| `classes/importer/` | Template importer for Elementor, Gutenberg, Divi, BB |
| `classes/logger/` | Logging system (WC logger integration) |
| `admin-core/` | All admin-facing PHP + React assets |
| `admin-core/api/` | REST API endpoint classes extending `ApiBase` |
| `admin-core/ajax/` | AJAX handler classes extending `AjaxBase` |
| `admin-core/inc/` | Admin menus, global settings, WP-CLI commands |
| `admin-core/assets/src/` | React source (editor-app + settings-app) |
| `admin-core/assets/build/` | Compiled JS/CSS (committed to repo) |
| `modules/` | Feature modules loaded conditionally |
| `compatibilities/` | Third-party theme/plugin integrations |
| `woocommerce/` | WooCommerce template overrides |
| `wizard/` | Setup wizard (separate webpack build) |
| `libraries/` | Vendored BSF packages (analytics, notices, nps-survey) |
| `tests/php/` | PHPUnit test cases |
| `tests/e2e/` | Playwright E2E tests |

## PHP Namespaces

| Namespace | Location | Purpose |
|-----------|----------|---------|
| `CartflowsAdmin\AdminCore\Api` | `admin-core/api/` | REST API controllers |
| `CartflowsAdmin\AdminCore\Ajax` | `admin-core/ajax/` | AJAX handlers |

Core classes in `classes/` use unprefixed globals following WordPress conventions.

## Admin Interface Architecture

Two independent React apps are compiled separately:

| App | Entry Point | Build Output | Purpose |
|-----|-------------|-------------|---------|
| **editor-app** | `EditorApp.js` | `editor-app.js` | Flow/step editor (canvas, node types, step settings) |
| **settings-app** | `SettingsApp.js` | `settings-app.js` | Dashboard, analytics, global settings, flows list |

Both apps use:
- **React 18** with `createRoot`
- **Redux** for global state
- **React Router** (v5) for in-app navigation
- **`@wordpress/api-fetch`** for REST API calls
- **Tailwind CSS** for styling

## REST API

All admin REST endpoints live under the `cartflows/v1` namespace. Every controller class extends the abstract `ApiBase` (which extends `WP_REST_Controller`).

See [REST-API-Reference](REST-API-Reference) for the full endpoint list.

## AJAX API

AJAX handlers use the action prefix `cartflows_`. Every handler class extends the abstract `AjaxBase`, which registers `wp_ajax_cartflows_{action}` hooks and handles nonce verification automatically.

See [AJAX-API-Reference](AJAX-API-Reference) for the full handler list.

## Module System

Each feature is a self-contained module under `modules/`. Modules register their own hooks, post meta, and templates. They are loaded by `Cartflows_Loader` on `plugins_loaded`.

See [Feature-Modules](Feature-Modules) for details on each module.

## Data Flow

```
Browser (React) ──► REST API (cartflows/v1) ──► PHP Controller
                                                      │
                                               WordPress DB
                                            (posts, postmeta,
                                             options table)

Browser (React) ──► AJAX (wp_ajax_cartflows_*)  ──► PHP Handler
```

## Related Pages

- [WordPress-Plugin-Structure](WordPress-Plugin-Structure)
- [Frontend-Architecture](Frontend-Architecture)
- [REST-API-Reference](REST-API-Reference)
- [AJAX-API-Reference](AJAX-API-Reference)
- [Feature-Modules](Feature-Modules)
- [Build-System](Build-System)
