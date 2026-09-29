# Optin Module -- Ability Mining

**Module:** Optin (Module 7)
**Priority:** P1
**Directory:** `cartflows/modules/optin/`
**WC Required:** Yes
**Date:** 2026-03-04

---

## Source Files Reviewed

- `class-cartflows-optin.php` -- Module loader
- `classes/class-cartflows-optin-meta-data.php` -- Meta fields, settings, design, product config
- `classes/class-cartflows-optin-markup.php` -- Frontend rendering, cart configuration, field handling

---

## Meta Keys Identified

| Key | Type | Description |
|-----|------|-------------|
| `wcf-optin-product` | array | Assigned product (simple, virtual, free) |
| `wcf-submit-button-text` | string | Submit button text |
| `wcf-optin-pass-fields` | yes/no | Pass fields as URL params |
| `wcf-optin-pass-specific-fields` | string | Comma-separated field names to pass |
| `wcf-optin-enable-custom-fields` | yes/no | Enable custom field editor |
| `wcf-optin-fields-billing` | array | Billing field configuration |
| `wcf-primary-color` | color | Primary color |
| `wcf-base-font-family` | string | Base font family |
| `wcf-input-fields-skins` | string | Input field style |
| `wcf-input-font-family` | string | Input font family |
| `wcf-input-font-weight` | string | Input font weight |
| `wcf-input-field-size` | string | Input field size |
| `wcf-field-tb-padding` | number | Input top/bottom padding |
| `wcf-field-lr-padding` | number | Input left/right padding |
| `wcf-field-color` | color | Input text color |
| `wcf-field-bg-color` | color | Input bg color |
| `wcf-field-border-color` | color | Input border color |
| `wcf-field-label-color` | color | Input label color |
| `wcf-submit-font-size` | number | Button font size |
| `wcf-button-font-family` | string | Button font family |
| `wcf-button-font-weight` | string | Button font weight |
| `wcf-submit-button-size` | string | Button size |
| `wcf-submit-tb-padding` | number | Button top/bottom padding |
| `wcf-submit-lr-padding` | number | Button left/right padding |
| `wcf-submit-button-position` | string | Button position (left/center/right) |
| `wcf-submit-color` | color | Button text color |
| `wcf-submit-hover-color` | color | Button hover text color |
| `wcf-submit-bg-color` | color | Button bg color |
| `wcf-submit-bg-hover-color` | color | Button bg hover color |
| `wcf-submit-border-color` | color | Button border color |
| `wcf-submit-border-hover-color` | color | Button border hover color |
| `wcf-disable-step` | yes/no | Disable step |
| `wcf-custom-script` | textarea | Custom script |

---

## Ability Candidates

### 1. get-optin-settings (Configuration/Read)
Returns full config of an optin step: assigned product, submit button text, pass-fields settings, and billing field list.

### 2. update-optin-product (Configuration/Write)
Sets the WooCommerce product for the optin step (must be simple, virtual, free).

### 3. update-optin-button-text (Configuration/Write)
Changes the submit button text on the optin form.

### 4. update-optin-pass-fields (Configuration/Write)
Configures URL parameter passing: enable toggle and specific field names.

---

## Rejected During Mining

- **Design/styling meta** (20+ color/font/size fields): Too granular for AI agent. Visual design tweaks are page builder territory.
- **Custom field editor**: Complex nested configuration requiring visual drag-drop. Not API-suitable.
- **Input field skins**: Very narrow design toggle.
- **Custom script**: Security concern.
- **Disable step**: Generic, low incremental value.
