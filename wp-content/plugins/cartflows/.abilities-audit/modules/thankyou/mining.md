# Thank You Module -- Ability Mining

**Module:** Thank You (Module 6)
**Priority:** P1
**Directory:** `cartflows/modules/thankyou/`
**WC Required:** Yes
**Date:** 2026-03-04

---

## Source Files Reviewed

- `class-cartflows-thankyou.php` -- Module loader
- `classes/class-cartflows-thankyou-meta-data.php` -- Meta fields, settings, design configuration
- `classes/class-cartflows-thankyou-markup.php` -- Frontend rendering, redirect logic, CSS generation

---

## Meta Keys Identified

| Key | Type | Description |
|-----|------|-------------|
| `wcf-tq-layout` | string (enum) | Skin: `legacy-tq-layout` or `modern-tq-layout` |
| `wcf-show-overview-section` | yes/no | Show order overview |
| `wcf-show-details-section` | yes/no | Show order details |
| `wcf-show-billing-section` | yes/no | Show billing details |
| `wcf-show-shipping-section` | yes/no | Show shipping details |
| `wcf-show-tq-redirect-section` | yes/no | Enable redirect after purchase |
| `wcf-tq-redirect-link` | URL | Redirect destination URL |
| `wcf-tq-text` | string | Custom thank you page text |
| `wcf-tq-primary-color` | color hex | Primary color |
| `wcf-tq-text-color` | color hex | Text color |
| `wcf-tq-font-family` | string | Text font family |
| `wcf-tq-font-size` | number | Text font size (px) |
| `wcf-tq-heading-color` | color hex | Heading color |
| `wcf-tq-heading-font-family` | string | Heading font family |
| `wcf-tq-heading-font-wt` | string | Heading font weight |
| `wcf-tq-container-width` | number | Container width (px) |
| `wcf-tq-section-bg-color` | color hex | Section background color |
| `wcf-enable-design-settings` | yes/no | Enable design settings |
| `wcf-disable-step` | yes/no | Disable step toggle |
| `wcf-custom-script` | textarea | Custom script |

---

## Ability Candidates

### 1. get-thankyou-settings (Configuration/Read)
Returns full configuration of a thank you step: layout, section visibility toggles, custom text, redirect settings, and design options.

### 2. update-thankyou-layout (Configuration/Write)
Changes the thank you skin/layout (legacy or modern).

### 3. update-thankyou-sections (Configuration/Write)
Toggles visibility of order sections: overview, details, billing, shipping.

### 4. update-thankyou-redirect (Configuration/Write)
Configures post-purchase redirect toggle and URL.

### 5. update-thankyou-custom-text (Configuration/Write)
Sets the custom thank you message text.

---

## Rejected During Mining (with reasons)

- **Design/styling meta** (colors, fonts, container width): Too granular for AI agent use. Design settings are visual tweaks better handled through the page builder UI. Low agent utility.
- **Custom script**: Security concern -- allowing arbitrary script injection via API is a safety risk.
- **Disable step**: Already available via generic step settings. Low incremental value.
- **Instant layout settings** (left/right bg colors, order summary position): Dependent on instant layout being enabled at flow level. Niche use case with low agent utility.
