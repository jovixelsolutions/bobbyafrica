# Landing Module — Implementation

**Date:** 2026-03-04
**Module:** Landing (Module 8)

## Implemented Abilities

### 1. `cartflows/get-landing-settings` (Read)

**Config entry added to:** `class-cartflows-ability-config.php`
**Runtime method added to:** `class-cartflows-ability-runtime.php`

- **Method:** `get_landing_settings( $input )`
- **Permission:** `cartflows_manage_flows_steps`
- **Input:** `step_id` (integer, required)
- **Output:** `step_id`, `slug`, `next_step_link`, `disable_step`
- **Validations:**
  - Step must exist and be `cartflows_step` post type
  - Step type must be `landing` (via `wcf-step-type` meta)
- **WooCommerce dependency:** None (landing pages don't require WC)
- **MCP annotations:** `readOnlyHint: true`, `idempotentHint: true`, `type: resource`

## PHP Syntax Verification

- `php -l class-cartflows-ability-config.php` — No syntax errors detected
- `php -l class-cartflows-ability-runtime.php` — No syntax errors detected

## Summary

| Metric | Count |
|--------|-------|
| Abilities implemented | 1 |
| Read abilities | 1 |
| Write abilities | 0 |
