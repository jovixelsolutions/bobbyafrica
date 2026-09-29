# Feature Modules

CartFlows organises its functionality into self-contained modules under the `modules/` directory. Each module registers its own WordPress hooks, post meta boxes, templates, and assets.

## Module List

| Module | Directory | Purpose |
|--------|-----------|---------|
| Flow | `modules/flow/` | Core funnel and step CPT registration |
| Checkout | `modules/checkout/` | WooCommerce checkout page functionality |
| Thank You | `modules/thankyou/` | Post-purchase thank you pages |
| Optin | `modules/optin/` | Lead capture / opt-in forms |
| Landing | `modules/landing/` | Landing / sales pages |
| Gutenberg | `modules/gutenberg/` | WordPress block editor integration |
| Elementor | `modules/elementor/` | Elementor widget integration |
| Beaver Builder | `modules/beaver-builder/` | Beaver Builder module integration |
| Bricks | `modules/bricks/` | Bricks page builder integration |
| Email Report | `modules/email-report/` | Scheduled funnel performance emails |
| WooCommerce Dynamic Flow | `modules/woo-dynamic-flow/` | Dynamic product-based flows |

---

## Flow Module

**Directory:** `modules/flow/`

The foundation module — registers the two custom post types that all other modules build on.

### Custom Post Types

| CPT | Slug | Description |
|-----|------|-------------|
| Flow (Funnel) | `wcf-flow` | Container for a complete sales funnel |
| Step | `wcf-step` | An individual page within a funnel |

### Classes

- `class-cartflows-flow.php` — Flow CPT registration and meta
- `class-cartflows-step.php` — Step CPT registration and meta
- `class-cartflows-loader.php` — Module loader
- `class-cartflows-permalink.php` — Custom permalink structure for steps

### Templates

- `default.php` — Default flow page template
- Canvas template — Full-width template for step pages

---

## Checkout Module

**Directory:** `modules/checkout/`

The most feature-rich module. Handles WooCommerce checkout customisation, field management, and dynamic CSS.

### Features

- Custom checkout form layouts (Modern Checkout, Instant Checkout)
- Field manager — add, remove, and reorder checkout fields
- Global checkout functionality
- Order bump (pre-checkout offers)
- Dynamic CSS generation based on settings

### Classes

| Class | Purpose |
|-------|---------|
| `class-cartflows-checkout-meta.php` | Meta boxes and settings fields |
| `class-cartflows-checkout-ajax.php` | Checkout AJAX handlers |
| `class-cartflows-checkout-fields.php` | Checkout field management |
| `class-cartflows-checkout-markup.php` | Checkout HTML rendering |
| `class-cartflows-global-checkout.php` | Global checkout functionality |

### Templates

- `checkout-form.php` — Main checkout form template
- Embed checkout templates
- Review order table templates
- Modern Checkout and Instant Checkout layouts

### Dynamic CSS

CSS is generated server-side based on admin settings and output inline on the frontend, allowing per-step styling without uploading static CSS files.

---

## Thank You Module

**Directory:** `modules/thankyou/`

Customises the post-purchase thank you page displayed after a successful order.

### Features

- Custom thank you page layouts
- Instant thank you page (shows order immediately)
- Order details display
- Product summary

### Classes

| Class | Purpose |
|-------|---------|
| `class-cartflows-thankyou-meta-data.php` | Settings and meta |
| `class-cartflows-thankyou-markup.php` | HTML rendering |

### Templates

- `instant-thankyou-order-details.php` — Instant thank you details
- Order summary, product summary templates

---

## Optin Module

**Directory:** `modules/optin/`

Provides lead capture forms (email opt-in) as funnel steps, integrated with WooCommerce and popular email marketing tools.

### Features

- Custom opt-in form with configurable fields
- Form submission handling
- Integration hooks for email marketing platforms
- Dynamic CSS styling

### Classes

| Class | Purpose |
|-------|---------|
| `class-cartflows-optin-meta.php` | Settings meta boxes |
| `class-cartflows-optin-fields.php` | Form field management |
| `class-cartflows-optin-markup.php` | Form HTML rendering |

### Templates

- `simple.php` — Standard opt-in form template

---

## Landing Module

**Directory:** `modules/landing/`

Simple landing/sales page step type. Provides a full-width, distraction-free page layout with no default WooCommerce functionality.

### Classes

| Class | Purpose |
|-------|---------|
| `class-cartflows-landing.php` | Main landing page class |
| `class-cartflows-landing-meta.php` | Settings meta boxes |
| `class-cartflows-landing-markup.php` | Page HTML rendering |

---

## Gutenberg Module

**Directory:** `modules/gutenberg/`

Registers Gutenberg (WordPress block editor) blocks for embedding CartFlows forms in standard WordPress content.

### Blocks

| Block | Purpose |
|-------|---------|
| Checkout Form | Embed checkout form in any page |
| Optin Form | Embed opt-in form in any page |
| Order Detail Form | Show order details |
| Next Step Button | Link to next funnel step |

### Classes

| Class | Purpose |
|-------|---------|
| `class-cartflows-block-loader.php` | Block registration |
| `class-cartflows-block-config.php` | Block configuration |
| `class-cartflows-block-js.php` | Block JS assets |
| `class-cartflows-gutenberg-editor.php` | Editor integration |
| `class-cartflows-spectra-compatibility.php` | Spectra (Ultimate Addons for Gutenberg) compatibility |
| `class-cartflows-block-helper.php` | Shared block utilities |

---

## Elementor Module

**Directory:** `modules/elementor/`

Registers Elementor widgets for CartFlows form elements.

### Widgets

| Widget | Purpose |
|--------|---------|
| Checkout Form | Elementor checkout form widget |
| Optin Form | Elementor opt-in widget |
| Order Details Form | Order details widget |
| Next Step Button | Button linking to next step |

Widget CSS is compiled and enqueued when Elementor is active.

---

## Beaver Builder Module

**Directory:** `modules/beaver-builder/`

Registers Beaver Builder modules for CartFlows elements.

### Modules

| Module | Purpose |
|--------|---------|
| Checkout Form | BB checkout form module |
| Order Details Form | BB order details module |

---

## Bricks Module

**Directory:** `modules/bricks/`

Registers Bricks Builder elements for CartFlows.

### Elements

| Element | Purpose |
|---------|---------|
| Optin Form | Bricks optin form element |
| Order Details Form | Bricks order details element |

Widget CSS is compiled and enqueued when Bricks is active.

---

## Email Report Module

**Directory:** `modules/email-report/`

Sends scheduled email reports with funnel performance statistics to site administrators.

### Features

- Configurable email schedule
- Per-funnel statistics in email body
- HTML email template with branding

### Classes

| Class | Purpose |
|-------|---------|
| `class-cartflows-admin-report-emails.php` | Email scheduling and sending |

### Templates

- `email-header.php`
- `email-body.php`
- `email-footer.php`
- `email-stat-content.php`
- `cf-pro-block.php`
- `other-product-block.php`

---

## WooCommerce Dynamic Flow Module

**Directory:** `modules/woo-dynamic-flow/`

Enables product-based automatic funnel routing — when a customer adds a specific product to cart, they are automatically sent through a configured funnel.

### Classes

| Class | Purpose |
|-------|---------|
| `class-cartflows-wd-flow-loader.php` | Module bootstrap |
| `class-cartflows-wd-flow-product-meta.php` | Per-product funnel settings |
| `class-cartflows-wd-flow-product-actions.php` | Product-triggered flow actions |
| `class-cartflows-wd-flow-actions.php` | Flow routing logic |
| `class-cartflows-wd-flow-shortcodes.php` | Dynamic flow shortcodes |

---

## Related Pages

- [Architecture-Overview](Architecture-Overview)
- [WordPress-Plugin-Structure](WordPress-Plugin-Structure)
- [Page-Builder-Integrations](Page-Builder-Integrations)
- [WooCommerce-Integration](WooCommerce-Integration)
- [Database-Schema](Database-Schema)
