# Thank You Module -- Implementation

**Module:** Thank You (Module 6)
**Date:** 2026-03-04

---

## Implemented Abilities

### 1. cartflows/get-thankyou-settings (Read)
- **Config:** Added to `class-cartflows-ability-config.php`
- **Runtime:** `get_thankyou_settings()` in `class-cartflows-ability-runtime.php`
- **Gate:** `cartflows_manage_flows_steps` capability
- **WC check:** `function_exists('WC')`
- **Step type validation:** Confirms `wcf-step-type === 'thankyou'`
- **Returns:** layout, sections (4 toggles), custom_text, redirect (enabled + url), design (9 settings)

### 2. cartflows/update-thankyou-layout (Write)
- **Config:** Added with `cartflows_abilities_api_write` gate
- **Runtime:** `update_thankyou_layout()` -- updates `wcf-tq-layout` meta
- **Enum:** `legacy-tq-layout`, `modern-tq-layout`

### 3. cartflows/update-thankyou-sections (Write)
- **Config:** Added with `cartflows_abilities_api_write` gate
- **Runtime:** `update_thankyou_sections()` -- merge-update pattern for 4 toggles
- **Meta keys:** `wcf-show-overview-section`, `wcf-show-details-section`, `wcf-show-billing-section`, `wcf-show-shipping-section`

### 4. cartflows/update-thankyou-redirect (Write)
- **Config:** Added with `cartflows_abilities_api_write` gate
- **Runtime:** `update_thankyou_redirect()` -- updates redirect enable toggle and URL
- **URL sanitization:** Uses `esc_url_raw()` for saving, `esc_url()` for output

### 5. cartflows/update-thankyou-custom-text (Write)
- **Config:** Added with `cartflows_abilities_api_write` gate
- **Runtime:** `update_thankyou_custom_text()` -- updates `wcf-tq-text` meta

---

## Files Modified

- `cartflows/abilities/class-cartflows-ability-config.php` -- 5 ability config entries added
- `cartflows/abilities/class-cartflows-ability-runtime.php` -- 5 execute callback methods added
