# Ability: `moderncart/complete-onboarding`

Marks the Modern Cart onboarding wizard as complete by setting the `moderncart_is_onboarding_complete` WordPress option to `"yes"`. This operation is **idempotent** — calling it when onboarding is already complete is safe and returns a success response.

---

## Metadata

| Field | Value |
|-------|-------|
| **ID** | `moderncart/complete-onboarding` |
| **Category** | `moderncart` |
| **Label** | Complete Onboarding |
| **Capability** | `manage_options` |
| **Type** | Write (idempotent) |
| **Destructive** | No |
| **Idempotent** | Yes |
| **Dry Run** | No |
| **Source** | `inc/abilities/plugin/complete-onboarding.php` |

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
  "success": true,
  "was_already_complete": false
}
```

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Always `true` on a successful call |
| `was_already_complete` | boolean | `true` if onboarding was already marked complete before this call; `false` if this call was the one to mark it complete |

---

## What This Does

Internally, the ability:

1. Reads the current value of `get_option( 'moderncart_is_onboarding_complete', 'no' )`
2. If the value is not `"yes"`, calls `update_option( 'moderncart_is_onboarding_complete', 'yes' )`
3. Returns `was_already_complete: true/false` to indicate whether the write actually occurred

After this call, `moderncart/get-plugin-status` will return `"is_onboarding_complete": true`.

---

## Error Responses

| Error Code | Condition |
|-----------|-----------|
| `moderncart_ability_forbidden` | Current user lacks `manage_options` capability |

---

## When to Call This

Call `complete-onboarding` at the **end of a programmatic setup flow** — after you have applied initial settings via `update-settings`. Doing so suppresses the onboarding wizard redirect that would otherwise appear in the WordPress admin.

### Typical usage sequence

```
moderncart/get-plugin-status
  → is_onboarding_complete: false

moderncart/get-available-options
  → discover valid enum values

moderncart/update-settings
  → apply initial configuration (e.g. enable cart, set colors)

moderncart/complete-onboarding
  → mark setup done; suppress onboarding redirect
```

---

## Idempotency Notes

- Calling this ability multiple times produces the same end state
- The `was_already_complete` field lets you distinguish a first-call completion from a redundant call
- No harm results from calling it when setup is already complete

---

## Related Abilities

- [Ability-Get-Plugin-Status](Ability-Get-Plugin-Status) — check `is_onboarding_complete` before and after
- [Ability-Update-Settings](Ability-Update-Settings) — apply initial configuration before completing onboarding
