# Landing Module -- Ability Mining

**Module:** Landing (Module 8)
**Priority:** P1
**Directory:** `cartflows/modules/landing/`
**WC Required:** No
**Date:** 2026-03-04

---

## Source Files Reviewed

- `class-cartflows-landing.php` -- Module loader
- `classes/class-cartflows-landing-meta-data.php` -- Meta fields, settings
- `classes/class-cartflows-landing-markup.php` -- Frontend rendering, redirect logic, homepage integration

---

## Meta Keys Identified

| Key | Type | Description |
|-----|------|-------------|
| `wcf-disable-step` | yes/no | Disable step toggle |
| `wcf-custom-script` | textarea | Custom script |
| `step_post_name` | string | Step slug (post field, not meta) |

---

## Ability Candidates

### 1. get-landing-settings (Configuration/Read)
Returns landing step configuration: step slug, next step link shortcode, and disable-step toggle. This is the only viable read ability for this minimal module.

---

## Rejected During Mining

- **Custom script**: Security concern -- arbitrary code injection via API.
- **Disable step toggle** (as write ability): Too generic (applies to all step types equally). Could be a general "update-step-settings" but that crosses module boundaries. Low value as a standalone write ability for landing only.
- **Next step link shortcode**: Read-only informational value, already available via get-step. Captured inside get-landing-settings instead.

---

## Notes

The Landing module is intentionally minimal -- it is a page-builder-driven step type with almost no CartFlows-specific configuration. The step content is entirely managed through the page builder (Elementor, Gutenberg, Divi, etc.). Only 1 ability is viable.
