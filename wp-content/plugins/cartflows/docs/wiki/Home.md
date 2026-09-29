# CartFlows Wiki

Welcome to the CartFlows developer documentation. CartFlows (v2.2.1) is a WordPress plugin that creates beautiful checkout pages and sales funnels for WooCommerce.

**Author:** Brainstorm Force
**Plugin URI:** https://cartflows.com/
**Requires:** WordPress 5.0+, WooCommerce 3.0+, PHP 7.2+

---

## Quick Navigation

### Getting Started
- [Getting-Started](Getting-Started) — Install, activate, and create your first funnel
- [Environment-Configuration](Environment-Configuration) — Local dev with wp-env/Docker
- [Architecture-Overview](Architecture-Overview) — How the plugin is structured

### Backend Development
- [WordPress-Plugin-Structure](WordPress-Plugin-Structure) — File naming, hooks, capabilities
- [Feature-Modules](Feature-Modules) — All 11 feature modules explained
- [WooCommerce-Integration](WooCommerce-Integration) — Cart, checkout, and order hooks
- [Database-Schema](Database-Schema) — Post types, taxonomies, meta, options

### API Reference
- [REST-API-Reference](REST-API-Reference) — All `cartflows/v1` endpoints
- [AJAX-API-Reference](AJAX-API-Reference) — All `wp_ajax_cartflows_*` handlers
- [WordPress-Hooks-Reference](WordPress-Hooks-Reference) — All actions and filters

### Frontend Development
- [Frontend-Architecture](Frontend-Architecture) — React 18, Redux, React Router overview
- [Editor-App](Editor-App) — Flow/step editor canvas and components
- [Settings-App](Settings-App) — Dashboard, analytics, and settings UI
- [State-Management](State-Management) — Redux store and SettingsProvider
- [Build-System](Build-System) — webpack, npm scripts, asset pipeline

### Integrations
- [Page-Builder-Integrations](Page-Builder-Integrations) — Elementor, Gutenberg, Beaver Builder, Bricks, Divi

### Standards & Process
- [WordPress-Coding-Standards](WordPress-Coding-Standards) — PHPCS, PHPStan, ESLint, Prettier
- [Testing-Guide](Testing-Guide) — PHPUnit, Playwright E2E, wp-env
- [Contributing-Guide](Contributing-Guide) — Branches, commits, PR process
- [Deployment-Guide](Deployment-Guide) — Build checklist, release procedure

### Reference
- [Troubleshooting-FAQ](Troubleshooting-FAQ) — Common issues and solutions
- [Changelog](Changelog) — Recent release notes

---

## Tech Stack at a Glance

| Layer | Technology |
|-------|-----------|
| Backend | PHP (WordPress plugin, PSR-4 namespaces) |
| Frontend | React 18 + Redux + React Router |
| Styling | Tailwind CSS + PostCSS |
| Build | Webpack via `@wordpress/scripts` |
| Code Quality | PHPCS (WordPress + VIP-Go) + PHPStan level 9 |
| E2E Testing | Playwright + wp-env |
| Unit Testing | PHPUnit 8 |
| Package Manager | npm + Composer |

---

## Key Directories

```
cartflows/
├── cartflows.php              # Plugin entry point
├── classes/                   # Core PHP classes (singleton loader, helpers)
├── admin-core/
│   ├── ajax/                  # AJAX handlers (AjaxBase subclasses)
│   ├── api/                   # REST endpoints (ApiBase subclasses)
│   ├── inc/                   # Admin menus, global settings, WP-CLI
│   └── assets/src/            # React source for editor-app + settings-app
├── modules/                   # Feature modules
├── compatibilities/           # Third-party compatibility patches
├── woocommerce/               # WooCommerce template overrides
├── wizard/                    # Setup wizard
├── tests/
│   ├── php/                   # PHPUnit tests
│   └── e2e/                   # Playwright E2E tests
└── docs/wiki/                 # This documentation
```

---

## Common Commands

```bash
# PHP
composer lint     # PHPCS check
composer phpstan  # Static analysis
composer test     # PHPUnit

# JavaScript
npm run build     # Production build
npm run start     # Dev watch mode
npm run lint-js   # ESLint

# Local environment
npm run env:start # Start wp-env Docker
npm run env:stop  # Stop wp-env Docker
npm run test:e2e  # Run E2E tests
```

---

## REST API Quick Reference

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/wp-json/cartflows/v1/admin/flows/` | List flows |
| GET | `/wp-json/cartflows/v1/admin/flow-data/{id}` | Get flow |
| GET | `/wp-json/cartflows/v1/admin/step-data/{id}` | Get step |
| GET | `/wp-json/cartflows/v1/admin/commonsettings/` | Get settings |
| GET | `/wp-json/cartflows/v1/admin/homepage/` | Get dashboard data |

See [REST-API-Reference](REST-API-Reference) for full details.
