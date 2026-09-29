# WooCommerce Cart Abandonment Recovery — Development Guide

## Plugin Description

**WooCommerce Cart Abandonment Recovery** helps recover lost revenue by capturing users' email addresses on the checkout page and sending automated follow-up emails if they do not complete their purchase. The plugin tracks abandoned carts, schedules and sends recovery emails, manages analytics, and provides admin interfaces for configuration and reporting.

---

## Folder Structure

```
woo-cart-abandonment-recovery/
├── .git/
├── .claude/
├── .github/
├── .wordpress-org/
├── admin/
│   ├── ajax/
│   ├── api/
│   ├── assets/
│   ├── bsf-analytics/
│   ├── build/
│   ├── inc/
│   ├── src/
│   │   ├── components/
│   │   │   ├── fields/
│   │   │   ├── followUpEmails/
│   │   │   ├── dashboard/
│   │   │   ├── detailedReport/
│   │   │   ├── integrations/
│   │   │   ├── Settings/
│   │   │   ├── common/
│   │   │   │   ├── empty-blocks/
│   │   │   │   └── skeletons/
│   │   ├── constants/
│   │   ├── pages/
│   │   ├── store/
│   │   └── utils/
│   ├── views/
│   │   └── settings-app.php
├── classes/
├── languages/
├── lib/
│   └── astra-notices/
├── modules/
│   ├── cart-abandonment/
│   │   ├── assets/
│   │   ├── classes/
│   │   └── includes/
│   └── weekly-email-report/
│       └── templates/
├── node_modules/
├── tests/
│   └── php/
│       └── stubs/
├── vendor/
├── AI_GUIDE.md
├── README.md
├── ... (various config and root files)
```

---

## Purpose of Each Folder

-   **.git, .claude, .github, .wordpress-org**: Version control, automation, and meta/configuration for development and distribution.
-   **admin/**: All admin-side PHP, JS, and assets for plugin settings, dashboards, analytics, and AJAX endpoints.
-   **classes/**: Core PHP classes for plugin logic, settings, helpers, and admin notices.
-   **languages/**: Translation files for internationalization.
-   **lib/**: Third-party or shared libraries (e.g., astra-notices for admin notifications).
-   **modules/**: Main business logic modules (cart abandonment, weekly email reports, etc.).
-   **node_modules/**: Node.js dependencies for build tools and frontend assets.
-   **tests/**: Test stubs and files for automated/unit testing.
-   **vendor/**: Composer-managed PHP dependencies.

---

## List of All Files and Their Purposes

### Root Files

-   **woo-cart-abandonment-recovery.php**: Main plugin bootstrap file, loads the loader class.
-   **class-wcar-admin-loader.php**: Admin loader for initializing admin-side classes and constants.
-   **uninstall.php**: Handles cleanup and database/table removal on plugin uninstall.
-   **readme.txt**: WordPress.org readme, plugin info, and changelog.
-   **changelog.txt**: Detailed changelog for releases.
-   **composer.json / composer.lock**: PHP dependency management.
-   **package.json / package-lock.json**: Node.js dependency management.
-   **phpcs.xml.dist**: PHP CodeSniffer config.
-   **Gruntfile.js, webpack.config.js, postcss.config.js, tailwind.config.js**: Build and asset pipeline configs.
-   **.editorconfig, .eslintignore, .eslintrc.js, .prettierignore, .prettierrc.js, .stylelintrc, .stylelintignore, .browserslistrc, .distignore, .gitignore**: Linting, formatting, and editor configs.
-   **component-data.json**: Likely used for frontend component configuration.
-   **phpinsights.php**: PHP Insights config or custom rules.
-   **AI_GUIDE.md**: Guide for AI agents and developers on plugin architecture and code understanding.
-   **README.md**: Placeholder for a standard project readme.

### Key Folders and Notable Files

#### classes/

-   **class-cartflows-ca-loader.php**: Main loader, initializes plugin, constants, and hooks.
-   **class-cartflows-ca-helper.php**: Utility/helper functions for cart abandonment logic.
-   **class-cartflows-ca-default-meta.php**: Centralized management of plugin default values and settings.
-   **class-cartflows-ca-settings.php**: Handles plugin settings and admin options.
-   **class-cartflows-ca-tabs.php**: Manages admin tabbed UI.
-   **class-cartflows-ca-update.php**: Handles plugin update logic.
-   **class-cartflows-ca-admin-notices.php**: Admin notice management.
-   **class-cartflows-ca-utils.php**: Miscellaneous utility functions.

#### admin/

-   **ajax/**: AJAX endpoints for admin actions (email templates, reports, settings, etc.).
-   **api/**: REST-like API endpoints for dashboard, reports, follow-ups, etc.
-   **assets/**: Admin CSS, JS, and images.
-   **bsf-analytics/**: Analytics integration (usage tracking, stats, loader).
-   **inc/**: Admin-side PHP helpers (meta options, admin logic).
-   **src/**: React-based admin UI (pages, components, store, utils, constants).
-   **views/**: PHP view templates for admin UI.

#### modules/

-   **cart-abandonment/**: Core cart abandonment logic, tracking, email scheduling, DB, and reporting.
    -   **classes/**: Tracking, cron, email templates, scheduling, DB, order table, settings.
    -   **includes/**: Report and tab logic, promotion page, single report details.
    -   **assets/**: Module-specific assets.
-   **weekly-email-report/**: Logic for weekly admin report emails.
    -   **templates/**: Email templates for weekly reports.

#### lib/astra-notices/

-   **class-astra-notices.php**: Admin notice system.
-   **notices.css / notices.js**: Styles and scripts for notices.
-   **composer.json**: Library metadata.

#### tests/php/stubs/

-   **wcar-stubs.php**: Stubs for testing plugin classes and functions.

#### admin/build/

-   **settings.js, settings.asset.php, settings.css**: Compiled JS/CSS and asset manifest for the React-based settings/admin UI.

#### admin/views/

-   **settings-app.php**: PHP entry point for the React-based settings app.

#### admin/src/

-   **components/**: Modular React components for admin UI, organized by feature (fields, followUpEmails, dashboard, etc.).
-   **pages/**: React page components for each admin section (FollowUpEmails, Integrations, Settings, etc.).
-   **store/**: State management for React admin UI.
-   **utils/**: Utility functions for React admin UI.
-   **constants/**: Shared constants for React admin UI.

---

## Class Structure (Key Classes)

-   **CARTFLOWS_CA_Loader**: Main plugin loader, singleton, initializes everything.
-   **Cartflows_Ca_Helper**: Utility methods for cart and order logic.
-   **Cartflows_Ca_Default_Meta**: Default settings and meta management.
-   **Cartflows_Ca_Settings**: Handles admin settings and options.
-   **Cartflows_Ca_Admin_Notices**: Admin notices and alerts.
-   **Cartflows_Ca_Module_Loader**: Loads all cart abandonment module classes.
-   **Cartflows_Ca_Tracking**: Tracks cart abandonment events and user actions.
-   **Cartflows_Ca_Email_Schedule**: Schedules and manages recovery emails.
-   **Cartflows_Ca_Email_Templates**: Manages email templates for recovery.
-   **Cartflows_Ca_Database**: Handles DB table creation and queries.
-   **Cartflows_Ca_Order_Table**: Admin UI for abandoned/recovered orders.
-   **Cartflows_Ca_Setting_Functions**: Miscellaneous settings logic (GDPR, coupons, etc.).
-   **Wcar_Admin**: Admin-side logic and hooks.
-   **BSF_Analytics, BSF_Analytics_Loader, BSF_Analytics_Stats**: Analytics and usage tracking.
-   **Astra_Notices**: Admin notice system (from lib).

---

## Categorization of Code by Function

-   **Initialization/Bootstrap**: `woo-cart-abandonment-recovery.php`, `class-cartflows-ca-loader.php`, `class-wcar-admin-loader.php`
-   **Settings/Configuration**: `class-cartflows-ca-settings.php`, `class-cartflows-ca-default-meta.php`, `admin/inc/meta-options.php`, `admin/src/pages/Settings.js`
-   **Cart Abandonment Tracking**: `modules/cart-abandonment/classes/class-cartflows-ca-tracking.php`, `class-cartflows-ca-helper.php`
-   **Email Scheduling/Management**: `modules/cart-abandonment/classes/class-cartflows-ca-email-schedule.php`, `class-cartflows-ca-email-templates.php`, `modules/weekly-email-report/`
-   **Database/Storage**: `modules/cart-abandonment/classes/class-cartflows-ca-database.php`, uninstall/install logic
-   **Admin UI (PHP)**: `admin/inc/wcar-admin.php`, `admin/views/`, `admin/api/`, `admin/ajax/`
-   **Admin UI (JS/React)**: `admin/src/` (pages, components, store, utils)
-   **Analytics/Tracking**: `admin/bsf-analytics/`
-   **Testing**: `tests/php/stubs/`
-   **Notices/Feedback**: `lib/astra-notices/`, `class-cartflows-ca-admin-notices.php`
-   **Build/Dev Tools**: Root config files, `node_modules/`, `package.json`, `Gruntfile.js`, etc.

---

## How to Use This File

This file is intended for AI agents (Cursor AI, Codex AI, Amazon Q, etc.) to:

-   Quickly understand the plugin's architecture and main responsibilities
-   Locate files and classes for specific features or bug fixes
-   Categorize and reason about code for refactoring, documentation, or automation
-   Serve as a reference for onboarding new developers or AI assistants

---

_Generated automatically for AI development and code understanding._
