# Ability: `moderncart/reset-settings`

Resets one or more Modern Cart setting groups back to factory defaults by deleting their stored values from the WordPress options table. On the next page load, the plugin re-reads defaults from `Helper::get_defaults()`.

> **This operation is irreversible.** Use [`moderncart/get-settings`](Ability-Get-Settings) first to save a backup of current values if needed. Use `dry_run: true` to preview which groups would be reset before committing.

---

## Metadata

| Field | Value |
|-------|-------|
| **ID** | `moderncart/reset-settings` |
| **Category** | `moderncart` |
| **Label** | Reset Settings to Defaults |
| **Capability** | `manage_options` |
| **Type** | Write (destructive) |
| **Destructive** | Yes |
| **Idempotent** | No |
| **Dry Run** | Yes |
| **Source** | `inc/abilities/settings/reset-settings.php` |

---

## Parameters

### `groups` _(array of strings, optional)_

The setting groups to reset. When omitted, **all four groups** are reset.

**Allowed values:**

| Value | Option Key Deleted |
|-------|-------------------|
| `moderncart_setting` | Core plugin toggles |
| `moderncart_cart` | Cart display and text labels |
| `moderncart_floating` | Floating cart position and colors |
| `moderncart_appearance` | Global colors and typography |

### `dry_run` _(boolean, optional, default: `false`)_

When `true`, returns a preview of which groups would be reset **without deleting anything**.

---

## Return Value

### On success (live run)

```json
{
  "reset_groups": ["moderncart_setting", "moderncart_cart"],
  "reset_count": 2,
  "message": "2 setting groups reset to factory defaults."
}
```

| Field | Description |
|-------|-------------|
| `reset_groups` | Array of option keys that were deleted |
| `reset_count` | Number of groups reset |
| `message` | Human-readable summary (singular/plural) |

### On dry run

```json
{
  "dry_run": true,
  "groups_to_reset": ["moderncart_setting", "moderncart_cart", "moderncart_floating", "moderncart_appearance"],
  "reset_count": 4
}
```

| Field | Description |
|-------|-------------|
| `dry_run` | Always `true` |
| `groups_to_reset` | Which groups would be reset |
| `reset_count` | Number of groups that would be reset |

---

## Error Responses

| Error Code | Condition |
|-----------|-----------|
| `moderncart_invalid_groups` | A `groups` array was provided but contained no valid group names |
| `moderncart_ability_forbidden` | Current user lacks `manage_options` capability |

---

## How Reset Works

Internally the ability calls WordPress `delete_option( $group )` for each requested group. After deletion:

- On the next request, `Helper::get_option()` calls `wp_parse_args( [], defaults )` and returns all defaults
- The deleted option is not re-written to the database until a settings save occurs
- The admin UI will display default values immediately after reset

---

## Usage Examples

### Preview a full reset (dry run)

```json
{
  "dry_run": true
}
```

### Reset only appearance settings

```json
{
  "groups": ["moderncart_appearance"]
}
```

### Reset core toggles and cart settings

```json
{
  "groups": ["moderncart_setting", "moderncart_cart"]
}
```

### Full reset of all groups

```json
{}
```

---

## Recommended Pre-Reset Workflow

1. Call `moderncart/get-settings` → save the result as a backup
2. Call `moderncart/reset-settings` with `dry_run: true` → confirm which groups will be affected
3. Call `moderncart/reset-settings` without `dry_run` → apply the reset
4. Optionally call `moderncart/get-settings` again to confirm defaults are active

---

## Related Abilities

- [Ability-Get-Settings](Ability-Get-Settings) — back up current settings before resetting
- [Ability-Update-Settings](Ability-Update-Settings) — restore specific values after reset
