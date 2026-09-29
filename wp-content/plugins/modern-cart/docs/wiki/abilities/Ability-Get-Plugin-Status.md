# Ability: `moderncart/get-plugin-status`

Returns the current state of the Modern Cart plugin installation: version numbers, Pro plugin presence, onboarding completion, and site maintenance mode. Use this as the **first call in any setup or diagnostic flow**.

---

## Metadata

| Field | Value |
|-------|-------|
| **ID** | `moderncart/get-plugin-status` |
| **Category** | `moderncart` |
| **Label** | Get Plugin Status |
| **Capability** | `manage_options` |
| **Type** | Read-only |
| **Destructive** | No |
| **Idempotent** | Yes |
| **Source** | `inc/abilities/plugin/get-plugin-status.php` |

---

## Parameters

None. This ability takes no input.

```json
{}
```

---

## Return Value

```json
{
  "version": {
    "current": "1.0.7",
    "previous": "1.0.6"
  },
  "pro_status": "not-installed",
  "is_onboarding_complete": true,
  "is_maintenance_mode": false
}
```

### Field Reference

#### `version` _(object)_

| Field | Type | Description |
|-------|------|-------------|
| `current` | string | Current installed version of Modern Cart (from `moderncart_version` option, falls back to `MODERNCART_VER` constant) |
| `previous` | string | Previous version before the last update. Empty string if no upgrade has occurred |

#### `pro_status` _(string)_

The installation and activation state of the Modern Cart Pro plugin (`modern-cart-woo`).

| Value | Meaning |
|-------|---------|
| `not-installed` | Pro plugin is not present on the server |
| `inactive` | Pro plugin is installed but not activated |
| `active` | Pro plugin is installed and active |

> **Note**: Pro features such as `cart_style: "popup"` and recommendation upsells are only available when `pro_status` is `"active"`.

#### `is_onboarding_complete` _(boolean)_

`true` if the onboarding wizard has been completed (option `moderncart_is_onboarding_complete` is `"yes"`). `false` if the user has not yet completed initial setup.

Use [`moderncart/complete-onboarding`](Ability-Complete-Onboarding) to mark onboarding as complete after performing setup programmatically.

#### `is_maintenance_mode` _(boolean)_

`true` if the site is currently in WordPress maintenance mode. When `true`, the cart may not function normally for visitors.

---

## Error Responses

| Error Code | Condition |
|-----------|-----------|
| `moderncart_ability_forbidden` | Current user lacks `manage_options` capability |

---

## Usage Examples

### Check plugin status

```json
{}
```

### Typical AI agent setup flow

```
1. moderncart/get-plugin-status
   → Check is_onboarding_complete, pro_status, version.current

2. If is_onboarding_complete is false:
   → moderncart/get-available-options  (discover valid enum values)
   → moderncart/update-settings        (apply initial configuration)
   → moderncart/complete-onboarding    (mark setup done)

3. If pro_status is "not-installed" and popup cart is needed:
   → Inform user that Modern Cart Pro is required
```

---

## Related Abilities

- [Ability-Complete-Onboarding](Ability-Complete-Onboarding) — mark onboarding as done after programmatic setup
- [Ability-Get-Settings](Ability-Get-Settings) — read current configuration
- [Abilities-Overview](Abilities-Overview) — full abilities index
