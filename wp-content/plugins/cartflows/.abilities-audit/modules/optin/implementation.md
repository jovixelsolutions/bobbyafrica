# Optin Module -- Implementation

**Module:** Optin (Module 7)
**Date:** 2026-03-04

---

## Implemented Abilities

### 1. cartflows/get-optin-settings (Read)
- **Config:** Added to `class-cartflows-ability-config.php`
- **Runtime:** `get_optin_settings()` in `class-cartflows-ability-runtime.php`
- **Gate:** `cartflows_manage_flows_steps` capability
- **WC check:** `function_exists('WC')`
- **Step type validation:** Confirms `wcf-step-type === 'optin'`
- **Returns:** product (id, name), button_text, pass_fields (enabled, specific_fields)

### 2. cartflows/update-optin-product (Write)
- **Config:** Added with `cartflows_abilities_api_write` gate
- **Runtime:** `update_optin_product()` -- validates product is simple, virtual, free, then updates `wcf-optin-product`
- **Validation:** Checks `is_type('simple')`, `is_virtual()`, `get_price() == 0`

### 3. cartflows/update-optin-button-text (Write)
- **Config:** Added with `cartflows_abilities_api_write` gate
- **Runtime:** `update_optin_button_text()` -- updates `wcf-submit-button-text` meta

### 4. cartflows/update-optin-pass-fields (Write)
- **Config:** Added with `cartflows_abilities_api_write` gate
- **Runtime:** `update_optin_pass_fields()` -- merge-update for `wcf-optin-pass-fields` and `wcf-optin-pass-specific-fields`

---

## Files Modified

- `cartflows/abilities/class-cartflows-ability-config.php` -- 4 ability config entries added
- `cartflows/abilities/class-cartflows-ability-runtime.php` -- 4 execute callback methods added
