# Getting Started

This guide walks you through installing CartFlows, setting up your first funnel, and understanding the core concepts.

## Requirements

| Requirement | Minimum |
|-------------|---------|
| WordPress | 5.0+ |
| WooCommerce | 3.0+ |
| PHP | 7.2+ |

## Installation

### For Developers (from source)

```bash
# 1. Clone into your WordPress plugins directory
git clone <repo-url> wp-content/plugins/cartflows
cd wp-content/plugins/cartflows

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Build the React admin apps
npm run build

# 5. Activate in WordPress admin
# WordPress Admin → Plugins → CartFlows → Activate
```

### For End Users

1. Download the CartFlows plugin ZIP
2. In WordPress Admin: **Plugins → Add New → Upload Plugin**
3. Upload the ZIP and click **Install Now**
4. Click **Activate Plugin**

## Setup Wizard

On first activation, CartFlows launches a setup wizard that guides you through:

1. **Welcome** — Plugin overview
2. **Page Builder** — Select your preferred page builder (Elementor, Gutenberg, Divi, Beaver Builder, Bricks, or Other)
3. **WooCommerce** — Verify WooCommerce is installed
4. **Analytics** — Optional non-sensitive data sharing
5. **Ready** — Create your first funnel

You can skip the wizard and configure settings later in **CartFlows → Settings**.

## Core Concepts

### Flows (Funnels)

A **Flow** is a container that groups multiple steps into a sequential sales funnel. Each flow has:
- A name and URL slug
- One or more steps in a defined order
- Analytics tracking for conversions and revenue

### Steps

A **Step** is an individual page within a funnel. There are six step types:

| Step Type | Purpose |
|-----------|---------|
| **Landing** | Sales/marketing page before the checkout |
| **Checkout** | WooCommerce checkout form |
| **Order Bump** | Pre-purchase add-on (displayed on the checkout page) |
| **Upsell** | Post-purchase upgrade offer |
| **Downsell** | Alternative offer if upsell is declined |
| **Thank You** | Post-purchase confirmation page |
| **Optin** | Lead capture form (email signup) |

### Flow Canvas

The **Flow Canvas** is the visual editor where you design your funnel:
- Each step appears as a node
- Drag to reorder steps
- Click a node to open its settings
- View per-step conversion analytics

## Creating Your First Funnel

### 1. Create a New Flow

1. Go to **CartFlows** in the WordPress admin sidebar
2. Click **Add New Flow**
3. Enter a flow name
4. Choose a template or start from scratch

### 2. Add Steps

In the flow canvas:
1. Click **+ Add Step**
2. Choose a step type (Checkout, Landing, Thank You, etc.)
3. Optionally import a pre-designed template from the CartFlows library
4. Click **Create Step**

### 3. Configure Each Step

Click any step node to open its settings:

**Checkout step settings:**
- Products to sell
- Checkout form fields (enable/disable, labels, order)
- Layout (Standard, Modern Checkout, Instant Checkout)
- Order bump products
- Design (colours, fonts)

**Thank You step settings:**
- Order details display
- Next step or external redirect

### 4. Design the Page

Click **Edit with [Page Builder]** to open your page builder:
- Use CartFlows blocks/widgets to embed checkout forms
- Customise the page layout and content
- The checkout form block is the core element — it renders the WooCommerce checkout

### 5. Publish and Test

1. Set the flow status to **Published**
2. Visit the checkout step URL to test the flow
3. Complete a test purchase using WooCommerce test mode

## Store Checkout (Global Checkout)

**Store Checkout** replaces the default WooCommerce checkout with a CartFlows-designed checkout for all purchases on your store:

1. Go to **CartFlows → Settings → General**
2. Enable **Store Checkout**
3. Select or create the flow to use as your global checkout

When enabled, the "Proceed to Checkout" button on the WooCommerce cart page will send customers through your CartFlows funnel instead of the default WooCommerce checkout.

## Global Settings

Go to **CartFlows → Settings** to configure:

| Tab | Settings |
|-----|---------|
| **General** | Default page builder, Store Checkout |
| **Permalink** | Flow and step URL slugs |
| **User Role Manager** | Who can manage flows and settings |
| **Integrations** | Facebook, Google Analytics, TikTok, Pinterest, Snapchat, Google Ads |
| **Other** | Data retention, email reports, analytics opt-in |

## Permalink Structure

CartFlows steps have their own URL structure:

```
https://yoursite.com/{flow-slug}/{step-slug}/
```

Default: `https://yoursite.com/wcf/my-funnel/checkout/`

Customise the slug base in **CartFlows → Settings → Permalink**.

After changing permalink settings, visit **Settings → Permalinks** in WordPress admin and click **Save Changes** to flush rewrite rules.

## Analytics Dashboard

Go to **CartFlows → Analytics** to view:
- Total conversions and page views
- Revenue per visit and revenue per unique visitor
- Offer and order bump conversions
- Optin form performance
- Top performing funnels
- Per-step conversion data

## Development Quick Start

For developers contributing to CartFlows:

```bash
# Start local WordPress environment
npm run env:start

# Watch and rebuild JS on change
npm run start

# Run tests
composer test
npm run test:e2e
```

See [Environment-Configuration](Environment-Configuration), [Contributing-Guide](Contributing-Guide), and [Testing-Guide](Testing-Guide).

## Related Pages

- [Architecture-Overview](Architecture-Overview)
- [Feature-Modules](Feature-Modules)
- [Environment-Configuration](Environment-Configuration)
- [WooCommerce-Integration](WooCommerce-Integration)
- [Troubleshooting-FAQ](Troubleshooting-FAQ)
