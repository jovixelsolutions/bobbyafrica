# Ability: `moderncart/update-settings`

Updates Modern Cart settings across one or more setting groups. Supports **partial updates** — only the keys you include are written; all other existing settings are preserved. The operation is destructive and supports `dry_run`.

---

## Metadata

| Field | Value |
|-------|-------|
| **ID** | `moderncart/update-settings` |
| **Category** | `moderncart` |
| **Label** | Set Modern Cart Settings |
| **Capability** | `manage_options` |
| **Type** | Write |
| **Destructive** | Yes |
| **Idempotent** | No |
| **Dry Run** | Yes |
| **Source** | `inc/abilities/settings/set-settings.php` |

---

## Parameters

### `settings` _(object, **required**)_

A grouped object where each key is a setting group name and each value is a partial or complete settings object for that group.

Only groups and keys included in the payload are updated. Unspecified groups and keys are untouched.

#### `settings.moderncart_setting` _(object, optional)_

Core plugin behavior and feature toggles.

| Key | Type | Options | Description |
|-----|------|---------|-------------|
| `enable_moderncart` | string | `all`, `wc_pages`, `disabled`, `specific` | Controls where Modern Cart appears on the site |
| `enable_powered_by` | boolean | `true`, `false` | Show/hide "Powered by Modern Cart" attribution |
| `enable_ajax_add_to_cart` | boolean | `true`, `false` | Enable AJAX add-to-cart behavior |
| `enable_free_shipping_bar` | boolean | `true`, `false` | Show/hide the free shipping progress bar |
| `enable_express_checkout` | boolean | `true`, `false` | Enable express checkout gateway integration |

#### `settings.moderncart_cart` _(object, optional)_

Cart display, behavior configuration, and all user-facing text labels.

| Key | Type | Options / Notes |
|-----|------|----------------|
| `cart_style` | string | `slideout` (free), `popup` (requires Pro) |
| `cart_theme_style` | string | `style1`, `style2`, `style3` |
| `product_image_size` | string | `thumbnail`, `medium`, `large` |
| `enable_coupon_field` | string | `show` (always visible), `minimize` (collapsible), `hide` |
| `section_styling` | string | `accordian` (collapsible), `default` (always expanded) |
| `main_title` | string | Cart drawer heading text — **translatable, do not overwrite** |
| `coupon_title` | string | Coupon section heading — **translatable** |
| `coupon_placeholder` | string | Coupon input placeholder — **translatable** |
| `animation_speed` | number | Slide animation duration in milliseconds |

> **Warning**: Text label keys (`main_title`, `coupon_title`, etc.) are translatable strings. Overwriting them breaks i18n. Only update these if you have a specific non-default value to set.

#### `settings.moderncart_floating` _(object, optional)_

Floating cart icon positioning and color scheme.

| Key | Type | Options |
|-----|------|---------|
| `floating_cart_position` | string | `bottom-right`, `bottom-left`, `top-right`, `top-left` |
| `floating_cart_bg_color` | string | Hex color, e.g. `#0284C7` |
| `floating_cart_icon_color` | string | Hex color |

#### `settings.moderncart_appearance` _(object, optional)_

Global visual styling and color scheme.

| Key | Type | Options |
|-----|------|---------|
| `primary_color` | string | Hex color — primary brand color |
| `cart_header_text_alignment` | string | `left`, `center`, `right` |
| `heading_color` | string | Hex color |
| `body_color` | string | Hex color |

> **Color values** must be valid hex strings (e.g. `#FF5733`). If Astra theme is active, colors may be stored as CSS variables — resolve them to hex before writing.

### `context` _(object, optional)_

Optional metadata attached to the update operation. Passed through to `MCW_ZipWP_Helper::update_settings()` but not stored.

### `dry_run` _(boolean, optional, default: `false`)_

When `true`, the ability simulates the update and returns `{ "dry_run": true }` without writing any changes.

---

## Return Value

On success:

```json
{
  "settings_updated": {
    "applied": {
      "moderncart_setting": ["enable_free_shipping_bar"],
      "moderncart_appearance": ["primary_color"]
    },
    "rejected": {},
    "skipped": {},
    "total_count": 2
  }
}
```

| Field | Description |
|-------|-------------|
| `applied` | Keys successfully written, grouped by setting group |
| `rejected` | Keys that failed validation, grouped by setting group |
| `skipped` | Keys that were ignored (e.g. unchanged values) |
| `total_count` | Total number of keys applied across all groups |

On dry run:

```json
{ "dry_run": true }
```

---

## Error Responses

| Error Code | Condition |
|-----------|-----------|
| `moderncart_helper_unavailable` | `MCW_ZipWP_Helper` class is not available |
| `moderncart_invalid_input` | `settings` key is missing, not an array, or empty |
| `moderncart_no_settings_updated` | No keys were applied (all rejected or skipped) |
| `moderncart_ability_forbidden` | Current user lacks `manage_options` capability |

---

## Usage Examples

### Enable the free shipping bar

```json
{
  "settings": {
    "moderncart_setting": {
      "enable_free_shipping_bar": true
    }
  }
}
```

### Change floating cart position and primary color

```json
{
  "settings": {
    "moderncart_floating": {
      "floating_cart_position": "bottom-left"
    },
    "moderncart_appearance": {
      "primary_color": "#E53E3E"
    }
  }
}
```

### Disable Modern Cart site-wide (dry run first)

```json
{
  "settings": {
    "moderncart_setting": {
      "enable_moderncart": "disabled"
    }
  },
  "dry_run": true
}
```

### Change coupon field to always visible

```json
{
  "settings": {
    "moderncart_cart": {
      "enable_coupon_field": "show"
    }
  }
}
```

---

## Important Constraints

- **Partial update only** — include only the keys you want to change
- **`popup` cart style requires Pro** — setting `cart_style: "popup"` without Modern Cart Pro installed will have no visual effect
- **Text labels break i18n** — avoid overwriting translatable string keys
- **Boolean type safety** — use `true`/`false` (not `"true"`/`"false"`)
- **Hex colors must be valid** — invalid hex values are rejected

---

## Related Abilities

- [Ability-Get-Settings](Ability-Get-Settings) — read current settings before updating
- [Ability-Get-Available-Options](Ability-Get-Available-Options) — discover valid enum values
- [Ability-Reset-Settings](Ability-Reset-Settings) — restore factory defaults
