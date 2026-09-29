# Ability: `moderncart/get-available-options`

Returns all valid enumerable choices for Modern Cart settings fields. Call this ability **before** `moderncart/update-settings` to discover which values are valid for dropdown/enum-type fields.

---

## Metadata

| Field | Value |
|-------|-------|
| **ID** | `moderncart/get-available-options` |
| **Category** | `moderncart` |
| **Label** | Get Available Setting Options |
| **Capability** | `manage_options` |
| **Type** | Read-only |
| **Destructive** | No |
| **Idempotent** | Yes |
| **Source** | `inc/abilities/settings/get-available-options.php` |

---

## Parameters

### `groups` _(array of strings, optional)_

Filter the response to specific setting groups. When omitted, all four groups are returned.

**Allowed values:** `moderncart_setting`, `moderncart_cart`, `moderncart_floating`, `moderncart_appearance`

---

## Return Value

An object keyed by group name. Each group contains setting field definitions with their valid option lists.

### Full Response Shape

```json
{
  "moderncart_setting": {
    "enable_moderncart": {
      "label": "Enable Modern Cart",
      "description": "Controls where Modern Cart appears on your site.",
      "type": "string",
      "options": [
        { "value": "all",       "label": "Entire Website" },
        { "value": "wc_pages",  "label": "WooCommerce Pages Only" },
        { "value": "disabled",  "label": "Disabled" }
      ]
    },
    "enable_powered_by": {
      "label": "Show Powered By",
      "description": "Show or hide the \"Powered by Modern Cart\" attribution.",
      "type": "boolean",
      "options": [
        { "value": true,  "label": "Yes" },
        { "value": false, "label": "No" }
      ]
    },
    "enable_ajax_add_to_cart": {
      "label": "AJAX Add to Cart",
      "type": "boolean",
      "options": [
        { "value": true,  "label": "Enabled" },
        { "value": false, "label": "Disabled" }
      ]
    },
    "enable_free_shipping_bar": {
      "label": "Free Shipping Bar",
      "type": "boolean",
      "options": [
        { "value": true,  "label": "Show" },
        { "value": false, "label": "Hide" }
      ]
    },
    "enable_express_checkout": {
      "label": "Express Checkout",
      "type": "boolean",
      "options": [
        { "value": true,  "label": "Enabled" },
        { "value": false, "label": "Disabled" }
      ]
    }
  },

  "moderncart_cart": {
    "cart_style": {
      "label": "Cart Style",
      "description": "Primary cart display mode. popup requires Modern Cart Pro.",
      "type": "string",
      "options": [
        { "value": "slideout", "label": "Slide-out (Side Cart)" },
        { "value": "popup",    "label": "Popup (requires Pro)" }
      ]
    },
    "cart_theme_style": {
      "label": "Cart Theme Style",
      "type": "string",
      "options": [
        { "value": "style1", "label": "Style 1 (Default)" },
        { "value": "style2", "label": "Style 2" },
        { "value": "style3", "label": "Style 3" }
      ]
    },
    "product_image_size": {
      "label": "Product Image Size",
      "type": "string",
      "options": [
        { "value": "thumbnail", "label": "Thumbnail" },
        { "value": "medium",    "label": "Medium (Default)" },
        { "value": "large",     "label": "Large" }
      ]
    },
    "enable_coupon_field": {
      "label": "Coupon Field",
      "type": "string",
      "options": [
        { "value": "show",     "label": "Always Visible" },
        { "value": "minimize", "label": "Collapsible (Default)" },
        { "value": "hide",     "label": "Hidden" }
      ]
    },
    "section_styling": {
      "label": "Section Styling",
      "type": "string",
      "options": [
        { "value": "accordian", "label": "Accordion (Default)" },
        { "value": "default",   "label": "Always Expanded" }
      ]
    }
  },

  "moderncart_floating": {
    "floating_cart_position": {
      "label": "Floating Cart Position",
      "type": "string",
      "options": [
        { "value": "bottom-right", "label": "Bottom Right (Default)" },
        { "value": "bottom-left",  "label": "Bottom Left" },
        { "value": "top-right",    "label": "Top Right" },
        { "value": "top-left",     "label": "Top Left" }
      ]
    }
  },

  "moderncart_appearance": {
    "cart_header_text_alignment": {
      "label": "Cart Header Text Alignment",
      "type": "string",
      "options": [
        { "value": "left",   "label": "Left" },
        { "value": "center", "label": "Center (Default)" },
        { "value": "right",  "label": "Right" }
      ]
    }
  }
}
```

---

## Quick Reference: All Enum Fields

| Group | Key | Valid Values |
|-------|-----|-------------|
| `moderncart_setting` | `enable_moderncart` | `all`, `wc_pages`, `disabled` |
| `moderncart_cart` | `cart_style` | `slideout`, `popup`* |
| `moderncart_cart` | `cart_theme_style` | `style1`, `style2`, `style3` |
| `moderncart_cart` | `product_image_size` | `thumbnail`, `medium`, `large` |
| `moderncart_cart` | `enable_coupon_field` | `show`, `minimize`, `hide` |
| `moderncart_cart` | `section_styling` | `accordian`, `default` |
| `moderncart_floating` | `floating_cart_position` | `bottom-right`, `bottom-left`, `top-right`, `top-left` |
| `moderncart_appearance` | `cart_header_text_alignment` | `left`, `center`, `right` |

_* `popup` requires Modern Cart Pro_

> **Note on typo**: `accordian` (not `accordion`) is the stored enum value for the accordion section styling. This is the value you must pass to `update-settings`.

---

## Error Responses

| Error Code | Condition |
|-----------|-----------|
| `moderncart_ability_forbidden` | Current user lacks `manage_options` capability |

---

## Usage Examples

### Get all available options

```json
{}
```

### Get only cart and floating options

```json
{
  "groups": ["moderncart_cart", "moderncart_floating"]
}
```

---

## Related Abilities

- [Ability-Update-Settings](Ability-Update-Settings) — use discovered values to update settings
- [Ability-Get-Settings](Ability-Get-Settings) — read current active values
