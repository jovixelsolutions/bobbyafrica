# Build System

CartFlows uses `@wordpress/scripts` (Webpack) for compiling two React apps, with Grunt for additional minification tasks. Build artifacts are **committed to the repository** — no CI build step is required for deployment.

## Two-App Architecture

Two React apps are compiled independently:

| App | Entry Point | Output Files | Used On |
|-----|-------------|-------------|---------|
| `editor-app` | `admin-core/assets/src/EditorApp.js` | `editor-app.js`, `editor-app.css`, `editor-app.asset.php` | Flow editor, step editor pages |
| `settings-app` | `admin-core/assets/src/SettingsApp.js` | `settings-app.js`, `settings-app.css`, `settings-app.asset.php` | Dashboard, analytics, global settings |

The setup wizard has its own separate build using `wizard-webpack-config.js`.

## Build Commands

```bash
# Build both apps for production
npm run build

# Development watch mode (rebuilds on file change)
npm run start

# Build the setup wizard only
npm run wizard-build

# Build all three (main apps + wizard)
npm run all-builds
```

## Webpack Configuration

The main apps use the default `@wordpress/scripts` Webpack configuration. No custom `webpack.config.js` is needed — `wp-scripts` discovers entry points automatically from `package.json` or the default pattern.

The wizard app uses a custom configuration:

```
wizard-webpack-config.js   →  wizard/ app build
```

## Asset Pipeline

```
admin-core/assets/src/
  ├── EditorApp.js          (React 18 entry)
  ├── SettingsApp.js        (React 18 entry)
  ├── common/               (shared components + SCSS)
  ├── fields/               (form field components)
  ├── flow-editor/          (canvas, nodes, edges)
  ├── step-editor/          (step settings panels)
  ├── settings-app/         (dashboard, analytics, flows list)
  ├── utils/                (SettingsProvider, helpers)
  └── importer/             (template library)

     ↓  npm run build

admin-core/assets/build/
  ├── editor-app.js
  ├── editor-app.css
  ├── editor-app.asset.php  (dependency manifest)
  ├── settings-app.js
  ├── settings-app.css
  └── settings-app.asset.php
```

## Asset Manifest Files

`@wordpress/scripts` generates a `.asset.php` file alongside each bundle. These PHP files export the bundle's dependencies and version hash, used by `wp_enqueue_script()`:

```php
// Example: editor-app.asset.php
return array(
    'dependencies' => array( 'react', 'react-dom', 'wp-components', ... ),
    'version'      => 'abc123',
);
```

## Committed Build Artifacts

Build artifacts in `admin-core/assets/build/` are **tracked in git**. This means:

- Deployment does not require a build step
- PRs that change React source **must** include updated build files
- Run `npm run build` before committing any JS/CSS changes

**Important:** RTL CSS files (`editor-app-rtl.css`, `settings-app-rtl.css`) were intentionally removed. Do not re-add them.

## CSS / PostCSS

Tailwind CSS is processed via PostCSS:

```
src/**/*.scss + tailwind.config.js
    ↓  postcss (via wp-scripts)
admin-core/assets/build/*.css
```

Global styles are imported in `common/all-config.scss`.

## JavaScript Linting

```bash
npm run lint-js          # Run ESLint (WordPress config)
npm run lint-js:fix      # Auto-fix ESLint + Prettier violations
```

Configuration: `@wordpress/eslint-plugin` with `wp-prettier` for formatting.

## CSS Linting

```bash
npm run lint-css         # Run Stylelint
npm run lint-css:fix     # Auto-fix Stylelint violations
```

## Prettier

```bash
npm run pretty           # Check formatting
npm run pretty:fix       # Auto-fix formatting
```

## Internationalisation (i18n)

```bash
npm run i18n             # Extract all translatable strings
npm run i18n:po          # Generate .po files
npm run i18n:mo          # Compile .mo files
npm run i18n:json        # Generate .json files for JS translations
```

Language-specific targets exist for: Dutch (nl), French (fr), German (de), Spanish (es), Italian (it), Portuguese (pt), Polish (pl).

## Grunt

Grunt is available for additional minification tasks not covered by `wp-scripts`. Gruntfile.js at the root defines minification targets. Grunt is not part of the primary build pipeline.

## Path Aliases

`@wordpress/scripts` Webpack config supports path aliases configured in `package.json` or via a custom Webpack config. The editor app uses aliases such as:

| Alias | Resolved Path |
|-------|--------------|
| `@Editor` | `admin-core/assets/src/flow-editor/` |
| `@Utils` | `admin-core/assets/src/utils/` |
| `@Alert` | `admin-core/assets/src/common/confirm-popup/` |

## Pre-commit Hook

Running `npm install` copies a git pre-commit hook that runs linting checks before each commit. Do not bypass with `--no-verify` unless explicitly approved.

## Related Pages

- [Frontend-Architecture](Frontend-Architecture)
- [Editor-App](Editor-App)
- [Settings-App](Settings-App)
- [Contributing-Guide](Contributing-Guide)
- [Deployment-Guide](Deployment-Guide)
