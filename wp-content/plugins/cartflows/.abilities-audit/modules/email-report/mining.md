# Email Report Module -- Ability Mining

**Module:** Email Report (Module 9)
**Priority:** P2
**Directory:** `cartflows/modules/email-report/`
**WC Required:** No
**Date:** 2026-03-04

---

## Source Files Reviewed

- `class-cartflows-admin-report-emails.php` -- Main module class (scheduling, sending, unsubscribe, stats retrieval)
- `templates/email-body.php` -- Email rendering template (not ability-relevant)

---

## Options Identified

| Option | Type | Description |
|--------|------|-------------|
| `cartflows_stats_report_emails` | string (`enable`/`disable`) | Master toggle for weekly email reports |
| `cartflows_stats_report_email_ids` | string (newline-separated) | Recipient email addresses |

---

## Ability Candidates

### 1. get-email-report-settings (Configuration/Read)
Returns the weekly email report configuration: whether reports are enabled, the list of recipient email addresses, and the next scheduled send time via ActionScheduler.

### 2. update-email-report-settings (Configuration/Write)
Toggles the weekly email report on or off and/or updates the list of recipient email addresses. The ActionScheduler cron is automatically managed by the module on next `admin_init`.

---

## Rejected During Mining

- **Send email now** (trigger): Side-effect heavy -- sends real emails. Not safe for AI agent access.
- **Unsubscribe single email**: Narrow use case already covered by updating the full recipient list via `update-email-report-settings`.
- **Get last week stats**: Already exposed via `cartflows/get-flow-stats` which uses the same `AdminHelper::get_earnings()`.

---

## Notes

The Email Report module is a settings-oriented module with no post types or complex data structures. The two abilities cover the full read/write surface of its configuration. The ActionScheduler integration provides schedule visibility in the read ability.
