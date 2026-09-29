# Frontend Architecture

CartFlows ships two independent React 18 admin applications — the **Editor App** and the **Settings App** — compiled with `@wordpress/scripts` and styled with Tailwind CSS.

## Overview

```
admin-core/assets/src/
├── EditorApp.js          ← Editor app entry (React root)
├── SettingsApp.js        ← Settings app entry (React root)
├── common/               ← Shared components + utilities
├── fields/               ← Reusable form field components
├── flow-editor/          ← Canvas, nodes, analytics (editor)
├── step-editor/          ← Step settings panels (editor)
├── settings-app/         ← Dashboard, analytics, flows list
├── importer/             ← Template import UI
├── store-importer/       ← Store import UI
└── utils/                ← SettingsProvider, helpers
```

## Technology Stack

| Library | Version | Purpose |
|---------|---------|---------|
| React | 18.3.1 | UI rendering |
| React DOM | 18.2.0 | DOM mounting |
| React Router DOM | 5.2.0 | In-app routing |
| Redux | 4.1.0 | Global state management |
| React Redux | 7.2.4 | Redux bindings |
| `@wordpress/api-fetch` | 5.x | Authenticated REST API calls |
| `@wordpress/components` | 27.x | WP UI component library |
| `@wordpress/data` | 9.x | WP data store integration |
| `@wordpress/hooks` | 3.x | WordPress hook system in JS |
| `@wordpress/i18n` | 4.x | Internationalisation |
| Tailwind CSS | 3.0 | Utility-first styling |
| react-flow-renderer | 10.3.17 | Flow canvas / node editor |
| ApexCharts | 4.x | Analytics charts |
| react-select | 5.x | Enhanced select inputs |
| dayjs | 1.x | Date handling |
| SortableJS | 1.14 | Drag-and-drop sorting |

## Editor App

**Entry:** `EditorApp.js`

```jsx
const WcfEditorAppRoot = createRoot( document.getElementById( 'wcf-editor-app' ) );
WcfEditorAppRoot.render(
  <SettingsProvider>
    <ConfirmDialogProvider>
      <MainEditor />
      <LoaderPopup />
    </ConfirmDialogProvider>
  </SettingsProvider>
);
```

The editor app mounts on the `#wcf-editor-app` DOM element rendered by WordPress admin pages. It provides the visual flow canvas and step configuration panels.

See [Editor-App](Editor-App) for component details.

## Settings App

**Entry:** `SettingsApp.js`

The settings app powers the CartFlows dashboard, analytics views, flows list, and global settings panel.

See [Settings-App](Settings-App) for component details.

## State Management

State is managed through two complementary systems:

1. **Redux** — Global application state (flows, steps, settings data)
2. **`SettingsProvider`** — React Context wrapper providing initial server-side data injected via `wp_localize_script`

See [State-Management](State-Management) for details.

## Routing

React Router DOM v5 handles in-app navigation without full page reloads:

```
/                    → Home / Dashboard
/flows               → Flows list
/flow/:id            → Flow editor (canvas)
/flow/:id/step/:sid  → Step editor
/analytics           → Analytics dashboard
/settings            → Global settings
```

## API Communication

All REST API calls use `@wordpress/api-fetch`, which automatically:
- Attaches the WordPress nonce header (`X-WP-Nonce`)
- Handles authentication
- Follows the `cartflows/v1` namespace

```js
import apiFetch from '@wordpress/api-fetch';

const flows = await apiFetch( {
    path: '/cartflows/v1/flows',
    method: 'POST',
    data: { /* payload */ },
} );
```

## Data Injection

Server-side data is passed to JavaScript via `wp_localize_script` and `wp_add_inline_script`. The global objects include:

| Global | Contents |
|--------|---------|
| `wcfEditorAppData` | Flow/step data, nonce, admin URL, feature flags |
| `wcfSettingsAppData` | Dashboard data, settings, feature flags |

## Shared Components (`common/`)

| Component | Purpose |
|-----------|---------|
| `global-settings/SettingsPage` | Global settings form |
| `global-settings/SettingsContent` | Settings content renderer |
| `main-navigation/NavMenu` | Admin sidebar navigation |
| `confirm-popup/ConfirmPopup` | Reusable confirmation dialog |
| `processing-popup/LoaderPopup` | Loading overlay |

## Field Components (`fields/`)

| Component | Purpose |
|-----------|---------|
| `RenderFields.js` | Field factory — renders the correct field type |
| `TextField` | Text input |
| `Select2Field` | Enhanced select (react-select) |
| `Tooltip` | Tooltip wrapper |
| Product repeater | Product search + select for offers |

## Build Output

```
admin-core/assets/build/
├── editor-app.js          # Bundled editor app
├── editor-app.css         # Editor app styles
├── editor-app.asset.php   # Dependency + version manifest
├── settings-app.js        # Bundled settings app
├── settings-app.css       # Settings app styles
└── settings-app.asset.php # Dependency + version manifest
```

## Related Pages

- [Editor-App](Editor-App)
- [Settings-App](Settings-App)
- [State-Management](State-Management)
- [Build-System](Build-System)
- [Architecture-Overview](Architecture-Overview)
