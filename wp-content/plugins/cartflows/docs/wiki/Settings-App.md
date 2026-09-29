# Settings App

The Settings App is a React 18 application powering the CartFlows admin dashboard, analytics views, flows list, and global settings. It is compiled to `admin-core/assets/build/settings-app.js`.

## Entry Point

**File:** `admin-core/assets/src/SettingsApp.js`

The settings app mounts on a dedicated DOM element rendered by the CartFlows admin menu page. It uses `SettingsRoute.js` for in-app navigation.

## Directory Structure

```
admin-core/assets/src/settings-app/
├── pages/
│   ├── HomePage.js          ← Analytics dashboard with charts
│   ├── FlowsPage.js         ← Flows list and management
│   └── Analytics.js         ← Detailed analytics view
├── hooks/
│   └── usePublishedFlows.js ← Custom hook for published flows data
├── data/
│   └── reducer.js           ← Redux reducer for settings state
└── utils/
    ├── analytics-helpers.js ← Analytics calculation utilities
    └── InputEvents.js       ← Input event handlers
```

## Pages

### Home Page (Analytics Dashboard)

**File:** `settings-app/pages/HomePage.js`

The home page is an analytics dashboard displaying key funnel metrics. It renders the following chart components:

**Conversion Metrics**
| Chart | Metric |
|-------|--------|
| `TotalConversions` | Total completed purchases |
| `TotalPageViews` | Total funnel page views |
| `MobileConversions` | Conversions from mobile devices |
| `LaptopConversions` | Conversions from desktop |
| `AverageOrderValue` | Average order value across funnels |

**Revenue Metrics**
| Chart | Metric |
|-------|--------|
| `RevenuePerUniqueVisitor` | Revenue generated per unique visitor |
| `RevenuePerVisit` | Revenue generated per visit |
| `OfferRevenue` | Revenue from upsell/downsell offers |
| `BumpOfferRevenue` | Revenue from order bumps |

**Offer Metrics**
| Chart | Metric |
|-------|--------|
| `OfferConversions` | Upsell/downsell acceptance rate |
| `BumpConversions` | Order bump acceptance rate |

**Optin Metrics**
| Chart | Metric |
|-------|--------|
| `OptinListGrowth` | Growth of opt-in subscribers over time |
| `OptinTotalSubmissions` | Total opt-in form submissions |
| `OptinConversionRate` | Opt-in form conversion rate |

**Summary Widgets**
| Widget | Purpose |
|--------|---------|
| `TopPerformingFunnels` | List of highest-converting funnels |
| `QuickActions` | Shortcuts to common tasks |
| `RecentOrders` | Latest WooCommerce orders |
| `ExtendYourStore` | Upsell / feature discovery widget |

### Flows Page

**File:** `settings-app/pages/FlowsPage.js`

Lists all CartFlows funnels with:
- Funnel name, status, and step count
- Quick edit and delete actions
- Create new flow button
- Filter by status (published, draft, trash)
- Pagination

### Analytics Page

**File:** `settings-app/pages/Analytics.js`

Detailed analytics broken down by funnel type:

**Analytics Tables**
| Table | Purpose |
|-------|---------|
| `FunnelsTable` | Per-funnel conversion metrics |
| `OptinOverviewTable` | Opt-in funnel performance overview |
| `StepsConversionTable` | Step-by-step conversion drop-off |
| `ProductConversionTable` | Per-product performance |

Sub-views: Flows analytics, Optin analytics, Conversions analytics.

## Global Settings

The settings app also hosts the global settings form:

**Directory:** `admin-core/assets/src/common/global-settings/`

| Component | Purpose |
|-----------|---------|
| `SettingsPage` | Settings page wrapper |
| `SettingsContent` | Settings field groups renderer |

Settings are saved via REST API to the `cartflows_global_settings` WordPress option.

## Navigation

**File:** `admin-core/assets/src/SettingsRoute.js`

React Router v5 handles navigation between settings app views:

| Route | Component |
|-------|-----------|
| `/` | Home / Dashboard |
| `/flows` | Flows list |
| `/analytics` | Analytics |
| `/settings` | Global settings |

## State Management

The settings app uses a Redux reducer (`data/reducer.js`) for managing:
- List of flows and their metadata
- Analytics data from the REST API
- UI state (active tab, loading states, filters)

Data is fetched via `@wordpress/api-fetch` calling `cartflows/v1` REST endpoints.

## Charts Library

Analytics charts use **ApexCharts** (v4.x) via the `react-apexcharts` wrapper. Charts are configured with:
- Area/line charts for time-series data
- Bar charts for comparison metrics
- Responsive configuration for different screen sizes

## Related Pages

- [Frontend-Architecture](Frontend-Architecture)
- [Editor-App](Editor-App)
- [State-Management](State-Management)
- [REST-API-Reference](REST-API-Reference)
- [Build-System](Build-System)
