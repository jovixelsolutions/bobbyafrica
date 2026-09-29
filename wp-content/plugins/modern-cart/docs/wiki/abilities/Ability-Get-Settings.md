# Ability: `moderncart/get-settings`

Retrieves all Modern Cart settings grouped by setting category. The response structure matches the `settings` input format of [`moderncart/update-settings`](Ability-Update-Settings), so the output can be read, modified, and passed directly back to update-settings without reformatting.

---

## Metadata

| Field | Value |
|-------|-------|
| **ID** | `moderncart/get-settings` |
| **Category** | `moderncart` |
| **Label** | Get Modern Cart Settings |
| **Capability** | `manage_options` |
| **Type** | Read-only |
| **Destructive** | No |
| **Idempotent** | Yes |
| **Source** | `inc/abilities/settings/get-settings.php` |

---

## Parameters

All parameters are optional.

### `include_groups` _(array of strings, optional)_

Limits the response to one or more setting groups. When omitted, all four groups are returned.

**Allowed values:**

| Value | Description |
|-------|-------------|
| `moderncart_setting` | Core plugin behavior and feature toggles |
| `moderncart_cart` | Cart display, behavior, and text labels |
| `moderncart_floating` | Floating cart icon position and colors |
| `moderncart_appearance` | Global colors and typography |

### `format` _(string, optional)_

Response format. Only `"grouped"` is supported (default). Returns settings keyed by group name.

---

## Return Value

A plain object keyed by the requested group names. Each group contains the current stored settings merged with plugin defaults.

```json
{
  "moderncart_setting": {
    "enable_moderncart": "all",
    "enable_powered_by": true,
    "enable_ajax_add_to_cart": true,
    "enable_free_shipping_bar": false,
    "enable_express_checkout": false
  },
  "moderncart_cart": {
    "cart_style": "slideout",
    "cart_theme_style": "style1",
    "product_image_size": "medium",
    "enable_coupon_field": "minimize",
    "section_styling": "accordian",
    "main_title": "Your Cart",
    "...": "..."
  },
  "moderncart_floating": {
    "floating_cart_position": "bottom-right",
    "...": "..."
  },
  "moderncart_appearance": {
    "primary_color": "#0284C7",
    "cart_header_text_alignment": "center",
    "...": "..."
  }
}
```

---

## Error Responses

| Error Code | Condition |
|-----------|-----------|
| `moderncart_helper_unavailable` | `MCW_ZipWP_Helper` class is not available — plugin may not be fully initialized |
| `moderncart_ability_forbidden` | Current user does not have `manage_options` capability |

---

## Usage Examples

### Fetch all settings

```json
{}
```

### Fetch only the core toggles

```json
{
  "include_groups": ["moderncart_setting"]
}
```

### Fetch appearance and cart settings

```json
{
  "include_groups": ["moderncart_appearance", "moderncart_cart"]
}
```

---

## Recommended Workflow

Use this ability as the **first step** in any AI-assisted setup flow:

1. Call `moderncart/get-plugin-status` to confirm the plugin is active and onboarding state
2. Call `moderncart/get-settings` to read current configuration
3. Call `moderncart/get-available-options` to discover valid enum values
4. Call `moderncart/update-settings` with only the keys you want to change

---

## Related Abilities

- [Ability-Update-Settings](Ability-Update-Settings) — write settings back
- [Ability-Get-Available-Options](Ability-Get-Available-Options) — discover valid enum values
- [Ability-Reset-Settings](Ability-Reset-Settings) — restore factory defaults
