# State Management

CartFlows uses a layered state management approach combining **Redux** for global application state and **React Context** (via `SettingsProvider`) for server-injected initial data.

## Overview

```
WordPress Server
  │  wp_localize_script( 'cartflows-editor-app', 'wcfEditorAppData', [...] )
  │  wp_localize_script( 'cartflows-settings-app', 'wcfSettingsAppData', [...] )
  ↓
React App Boot
  │
  ├── SettingsProvider (Context)  ← wraps entire app
  │     └── initialData.js       ← reads window.wcf*AppData
  │
  └── Redux Store
        └── reducer(s)           ← handles UI + async data updates
```

## SettingsProvider

**Location:** `admin-core/assets/src/utils/SettingsProvider/`

`SettingsProvider` is a React Context provider that makes server-injected data available throughout the component tree without prop drilling.

```jsx
// Usage — wraps entire app in EditorApp.js
<SettingsProvider>
    <MainEditor />
</SettingsProvider>
```

**`initialData.js`** reads the global `wcfEditorAppData` or `wcfSettingsAppData` object injected by PHP and provides it as the context's initial value.

### What SettingsProvider provides

- Plugin settings (global CartFlows options)
- Current flow/step data
- Feature flags (pro features enabled/disabled)
- Admin URLs, REST nonce, AJAX URL
- Page builder detection

## Redux Store

The settings app maintains a Redux store with a reducer in `settings-app/data/reducer.js`.

### State Shape (Settings App)

```js
{
  flows: [],           // All flows/funnels
  flowsStats: {},      // Per-flow statistics
  analytics: {},       // Analytics data
  globalSettings: {},  // CartFlows global settings
  ui: {
    activeTab: '',     // Currently active navigation tab
    isLoading: false,  // Global loading indicator
    filters: {}        // Flow list filters
  }
}
```

### Action Pattern

The settings app dispatches standard Redux actions to update state. API calls are made with `@wordpress/api-fetch` and the results dispatched to the store:

```js
// Typical async fetch pattern
const response = await apiFetch( {
    path: '/cartflows/v1/flows',
    method: 'POST',
} );
dispatch( { type: 'SET_FLOWS', payload: response.flows } );
```

## Data Flow

```
REST API response
      │
      ▼
Redux dispatch
      │
      ▼
Reducer updates state
      │
      ▼
Connected components re-render
```

## `@wordpress/data` Integration

CartFlows uses `@wordpress/data` alongside Redux for integration with the WordPress core data layer. This provides access to:
- `wp.data.select( 'core' )` — WordPress core entities
- `wp.data.dispatch( 'core/notices' )` — Admin notice system

## Custom Hooks

### `usePublishedFlows`

**Location:** `settings-app/hooks/usePublishedFlows.js`

A custom React hook that fetches and returns the list of published flows from the REST API. It handles loading and error states internally.

```js
const { flows, isLoading, error } = usePublishedFlows();
```

## Event System

The editor app uses a custom `settingsEvents` event emitter (imported from the settings module) for cross-component communication that doesn't fit neatly into the Redux model (e.g., triggering save from a toolbar button).

```js
import settingsEvents from '@Utils/settingsEvents';

// Emit
settingsEvents.emit( 'save' );

// Listen
settingsEvents.on( 'save', handleSave );
```

## Context vs Redux — When to Use Each

| Use Case | Solution |
|----------|---------|
| Server-injected initial data | `SettingsProvider` (Context) |
| Feature flags, admin URLs, nonce | `SettingsProvider` (Context) |
| Dynamic UI state (loading, tabs) | Redux |
| Fetched API data (flows, analytics) | Redux |
| Cross-component events (save, reload) | `settingsEvents` emitter |
| Confirmation dialogs | `ConfirmDialogProvider` (Context) |

## Related Pages

- [Frontend-Architecture](Frontend-Architecture)
- [Editor-App](Editor-App)
- [Settings-App](Settings-App)
- [REST-API-Reference](REST-API-Reference)
