# Editor App

The Editor App is a React 18 application that provides the visual flow canvas and step configuration interface. It is compiled to `admin-core/assets/build/editor-app.js`.

## Entry Point

**File:** `admin-core/assets/src/EditorApp.js`

```jsx
import { createRoot } from 'react-dom/client';
import { SettingsProvider } from '@Utils/SettingsProvider';
import MainEditor from '@Editor/MainEditor';
import { ConfirmDialogProvider } from '@Alert/ConfirmDialog';
import LoaderPopup from './common/processing-popup/LoaderPopup';

const WcfEditorAppRoot = createRoot(
    document.getElementById( 'wcf-editor-app' )
);
WcfEditorAppRoot.render(
    <SettingsProvider>
        <ConfirmDialogProvider>
            <MainEditor />
            <LoaderPopup />
        </ConfirmDialogProvider>
    </SettingsProvider>
);
```

## Component Hierarchy

```
EditorApp.js
└── SettingsProvider (Context)
    └── ConfirmDialogProvider
        ├── MainEditor
        │   ├── Flow Editor (canvas view)
        │   │   ├── StepsPage
        │   │   ├── StepsNavigation
        │   │   ├── AnalyticsPage
        │   │   ├── FlowSettings
        │   │   └── Canvas
        │   │       ├── Node Types
        │   │       │   ├── CheckoutNode
        │   │       │   ├── LandingNode
        │   │       │   ├── OptinNode
        │   │       │   ├── ThankyouNode
        │   │       │   ├── ConditionalNode
        │   │       │   └── OfferNode
        │   │       └── Custom Edges
        │   └── Step Editor (step settings view)
        │       ├── Page settings panel
        │       ├── Field panel
        │       ├── CheckoutFormFields
        │       ├── OptinFormFields
        │       └── Order bump components
        └── LoaderPopup
```

## Flow Editor

**Directory:** `admin-core/assets/src/flow-editor/`

The flow editor renders a visual canvas using **react-flow-renderer** (v10.3.17). Each step in a funnel is represented as a node on the canvas.

### Node Types

| Node Type | Step Type | Description |
|-----------|-----------|-------------|
| `CheckoutNode` | Checkout | WooCommerce checkout step |
| `LandingNode` | Landing | Landing/sales page step |
| `OptinNode` | Optin | Lead capture / opt-in step |
| `ThankyouNode` | Thank You | Post-purchase thank you page |
| `ConditionalNode` | Conditional | Branching logic node |
| `OfferNode` | Offer | Upsell/downsell offer step |

### Canvas Components

| Component | Purpose |
|-----------|---------|
| `StepsPage` | Main canvas page with react-flow-renderer |
| `StepsNavigation` | Step list / navigation sidebar |
| `AnalyticsPage` | Per-flow analytics view |
| `FlowSettings.js` | Flow-level settings panel |
| Custom edges | Animated connection lines between nodes |
| Canvas helpers | Utilities for node positioning and layout |

## Step Editor

**Directory:** `admin-core/assets/src/step-editor/`

When a user clicks a step node, the step editor opens with configuration panels specific to that step type.

### Panels

| Panel | Purpose |
|-------|---------|
| `CheckoutFormFields` | Checkout field configuration (billing, shipping, custom) |
| `OptinFormFields` | Opt-in form field configuration |
| Page settings | Page layout, style, and template settings |
| Field panel | Individual field settings (label, placeholder, required) |
| Custom editor | Block/page builder integration |
| Field settings | Advanced field options |

### Order Bump Components

The step editor includes order bump (pre-purchase offer) components:

| Component | Purpose |
|-----------|---------|
| `ObInputEvents` | Order bump field event handlers |
| `OrderBumpPreview` | Live preview of the order bump |

## Template Importer

**Directory:** `admin-core/assets/src/importer/`

The importer UI allows users to browse and import pre-built funnel templates:

| Component | Purpose |
|-----------|---------|
| Library templates | Browse the CartFlows template library |
| Import buttons | Trigger template import |
| Creator buttons | Create steps from scratch |
| Popup templates | Template preview popups |

## Reusable Utilities

| Utility | Location | Purpose |
|---------|----------|---------|
| `InputEvents` | `editor-app/InputEvents.js` | Common field input event handlers |
| `SettingsProvider` | `utils/SettingsProvider/` | Context providing global settings state |
| `ConfirmDialog` | `common/confirm-popup/` | Async confirmation dialog hook |

## Events System

The editor app uses `settingsEvents` (a custom event emitter imported from the settings module) to communicate between decoupled components without prop drilling.

## Related Pages

- [Frontend-Architecture](Frontend-Architecture)
- [Settings-App](Settings-App)
- [State-Management](State-Management)
- [Feature-Modules](Feature-Modules)
- [Build-System](Build-System)
