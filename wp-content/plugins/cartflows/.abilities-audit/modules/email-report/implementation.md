# Email Report Module -- Implementation

**Date:** 2026-03-04
**Module:** Email Report (Module 9)

## Implemented Abilities

### 1. `cartflows/get-email-report-settings` (Read)

**Config entry added to:** `class-cartflows-ability-config.php`
**Runtime method added to:** `class-cartflows-ability-runtime.php`

- **Method:** `get_email_report_settings( $input )`
- **Permission:** `cartflows_manage_flows_steps`
- **Input:** None (global settings)
- **Output:** `enabled` (string), `email_ids` (array of strings), `next_scheduled` (ISO 8601 string)
- **Data sources:**
  - `get_option('cartflows_stats_report_emails')` for enabled status
  - `get_option('cartflows_stats_report_email_ids')` for recipient list
  - `as_next_scheduled_action('cartflows_send_report_summary_email')` for schedule
- **WooCommerce dependency:** None
- **MCP annotations:** `readOnlyHint: true`, `idempotentHint: true`, `type: resource`

### 2. `cartflows/update-email-report-settings` (Write)

**Config entry added to:** `class-cartflows-ability-config.php`
**Runtime method added to:** `class-cartflows-ability-runtime.php`

- **Method:** `update_email_report_settings( $input )`
- **Permission:** `cartflows_abilities_api_write` + `cartflows_manage_flows_steps`
- **Input:** `enabled` (string, optional, enum: enable/disable), `email_ids` (array of strings, optional)
- **Output:** `enabled` (string), `email_ids` (array of strings), `message` (string)
- **Validations:**
  - Enabled value must be 'enable' or 'disable'
  - Email addresses sanitized via `sanitize_email()`
  - Empty/invalid emails filtered out
- **WooCommerce dependency:** None
- **MCP annotations:** `readOnlyHint: false`, `idempotentHint: true`, `type: tool`

## Summary

| Metric | Count |
|--------|-------|
| Abilities implemented | 2 |
| Read abilities | 1 |
| Write abilities | 1 |
