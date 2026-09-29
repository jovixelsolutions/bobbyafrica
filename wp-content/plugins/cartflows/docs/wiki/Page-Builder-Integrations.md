# Page Builder Integrations

CartFlows integrates with five major WordPress page builders, allowing users to design checkout pages, opt-in forms, and landing pages using their preferred tool.

## Supported Page Builders

| Page Builder | Integration Type | Module |
|-------------|-----------------|--------|
| Gutenberg (Block Editor) | Native Blocks | `modules/gutenberg/` |
| Elementor | Custom Widgets | `modules/elementor/` |
| Beaver Builder | Custom Modules | `modules/beaver-builder/` |
| Bricks | Custom Elements | `modules/bricks/` |
| Divi | Template Importer | `classes/importer/` |

---

## Gutenberg (WordPress Block Editor)

**Module:** `modules/gutenberg/`

CartFlows registers four native Gutenberg blocks:

| Block Name | Block Key | Purpose |
|-----------|-----------|---------|
| Checkout Form | `cartflows/checkout-form` | Embeds CartFlows checkout in any page |
| Optin Form | `cartflows/optin-form` | Embeds opt-in form in any page |
| Order Detail Form | `cartflows/order-detail-form` | Shows order details after purchase |
| Next Step Button | `cartflows/next-step-button` | Navigation button between funnel steps |

### Registration

Blocks are registered via `class-cartflows-block-loader.php` which calls `register_block_type()` for each block using the compiled block assets in `modules/gutenberg/build/`.

### Spectra Compatibility

`class-cartflows-spectra-compatibility.php` handles conflicts and enhancements when **Spectra** (Ultimate Addons for Gutenberg) is also active.

---

## Elementor

**Module:** `modules/elementor/`

CartFlows registers four Elementor widgets extending `\Elementor\Widget_Base`:

| Widget | Class | Purpose |
|--------|-------|---------|
| Checkout Form | Checkout Form Widget | Full checkout form |
| Optin Form | Optin Form Widget | Lead capture form |
| Order Details Form | Order Details Widget | Post-purchase details |
| Next Step Button | Next Step Widget | Funnel navigation button |

### Registration

Widgets are registered on the `elementor/widgets/widgets_registered` hook. The module checks for Elementor's presence before loading.

### Styling

Widget CSS is compiled and enqueued separately when Elementor is active. Each widget supports Elementor's styling controls (colours, typography, spacing) where applicable.

---

## Beaver Builder

**Module:** `modules/beaver-builder/`

CartFlows registers two Beaver Builder modules extending `FLBuilderModule`:

| Module | Purpose |
|--------|---------|
| Checkout Form | Checkout form for BB pages |
| Order Details Form | Order details display for BB pages |

### Registration

Modules are registered using `FLBuilder::register_module()`. The integration loads only when Beaver Builder is active.

---

## Bricks

**Module:** `modules/bricks/`

CartFlows registers custom Bricks elements extending Bricks' element base class:

| Element | Purpose |
|---------|---------|
| Optin Form | Opt-in form element for Bricks |
| Order Details Form | Order details element for Bricks |

### Registration

Elements are registered using Bricks' element registration API. Widget CSS is enqueued when Bricks is active.

---

## Divi

**Integration:** Template importer (`classes/importer/`)

Divi is supported through the template import system rather than a dedicated module. When importing a CartFlows template designed for Divi:

1. `class-cartflows-importer.php` detects the Divi template format
2. Template JSON is processed by the Divi batch importer
3. Divi section and module settings are imported into the step's content

**File:** `classes/importer/class-cartflows-divi-batch-process.php`

---

## Template Importer

All page builders benefit from the CartFlows Template Library, accessible via the **Import** button in the flow canvas:

```
Flow Canvas → Add Step → Choose Template
```

The importer (`classes/importer/`) handles:
- Fetching templates from the CartFlows API
- Detecting the active page builder
- Dispatching to the correct builder-specific batch importer
- Background processing for large imports

### Supported Importers

| Builder | Batch Importer Class |
|---------|---------------------|
| Elementor | `class-cartflows-elementor-batch-process.php` |
| Gutenberg | `class-cartflows-gutenberg-batch-process.php` |
| Divi | `class-cartflows-divi-batch-process.php` |
| Beaver Builder | Handled via BB's import API |

---

## Compatibilities Directory

In addition to modules, the `compatibilities/` directory contains compatibility patches for specific theme and plugin combinations. These are separate from the page builder modules and handle edge cases such as:

- Theme-specific styling conflicts
- WooCommerce plugin conflicts
- Caching plugin integrations
- Payment gateway compatibility

---

## Detecting the Active Page Builder

CartFlows uses helper functions to detect which page builder is active on a given step:

```php
// Example helper pattern
CartFlows_Helper::get_step_type_template( $step_id );
```

The active page builder is stored in step post meta and used to determine which editor to load and which template format to use.

---

## Related Pages

- [Feature-Modules](Feature-Modules)
- [Architecture-Overview](Architecture-Overview)
- [WordPress-Plugin-Structure](WordPress-Plugin-Structure)
- [WooCommerce-Integration](WooCommerce-Integration)
