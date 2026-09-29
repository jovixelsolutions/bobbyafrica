# Expert Review — Module: Plugin Status & Diagnostics

---

## Candidate 1: `moderncart/get-plugin-status`

**Label:** Get Plugin Status
**Description:** Returns Modern Cart plugin version, Pro plugin installation status, onboarding completion, and site maintenance mode state.
**Category:** Configuration (read)
**Input:** none required
**Output:** version_current, version_previous, pro_status (not-installed|inactive|active), is_onboarding_complete, is_maintenance_mode
**Permission:** manage_options
**Annotations:** readonly=true, idempotent=true, destructive=false

### Expert 1 — UX/AI (16/18)
- ✅ Clear trigger: "Is Pro active?", "What version is installed?", "Has onboarding been done?"
- ✅ Zero-input simplicity — no ambiguity in construction
- ✅ Output drives next actions: if onboarding incomplete → run complete-onboarding; if Pro inactive → upsell
- ✅ Label and description are self-explanatory
- Criterion scores: Trigger=3, Construction=3, Output=3, Labels=3, Scope=2, Safety=2 → 16

### Expert 2 — WordPress (17/18)
- ✅ manage_options is correct (version/status info is admin-only)
- ✅ All reads — no sanitization risk, no performance issue
- ✅ Helper::get_pro_status() and MCW_ZipWP_Helper::is_onboarding_complete() already tested patterns
- ✅ is_maintenance_mode() is robust (handles Elementor, WooCommerce, WP core)
- Criterion scores: Capability=3, Standards=3, Edge=3, Sanitize=3, Performance=3, N+1=2 → 17

### Expert 3 — API Design (16/18)
- ✅ Minimal input (none required), clean flat output object
- ✅ Atomic and well-scoped (one concept: "is the plugin healthy/configured?")
- ✅ Composable: always run first in a setup flow before update-settings
- ✅ idempotentHint=true correct — deterministic for same state
- ✅ Naming follows verb-noun convention
- Criterion scores: InputMin=3, OutputClean=3, Atomic=3, Composable=3, Naming=2, Meta=2 → 16

**Average: 16.3/18 — STRONG ✅**

---

## Candidate 2: `moderncart/complete-onboarding`

**Label:** Complete Onboarding
**Description:** Marks the Modern Cart onboarding wizard as complete. Call after the initial plugin setup has been performed via update-settings.
**Category:** Configuration (write)
**Input:** none required
**Output:** success boolean, was_already_complete boolean
**Permission:** manage_options
**Annotations:** readonly=false, idempotent=true (calling twice is safe), destructive=false

### Expert 1 — UX/AI (13/18)
- ✅ Clear trigger: used at end of a setup flow (after update-settings)
- ✅ Zero input needed
- ⚠️ Value is limited on its own — only useful when AI is orchestrating initial setup
- ⚠️ Without knowing state, AI might call this unnecessarily
- Criterion scores: Trigger=2, Construction=3, Output=2, Labels=2, Scope=2, Safety=2 → 13

### Expert 2 — WordPress (15/18)
- ✅ manage_options correct
- ✅ Sets option `moderncart_is_onboarding_complete` to 'yes'
- ✅ Idempotent — safe to call multiple times
- ✅ Mirrors the existing `moderncart_complete_onboarding` AJAX action
- Criterion scores: Capability=3, Standards=3, Edge=2, Sanitize=3, Performance=3, N+1=1 → 15

### Expert 3 — API Design (12/18)
- ⚠️ Very narrow scope — just flips one DB flag
- ✅ Atomic
- ⚠️ Callers need get-plugin-status first to know if it's needed
- ⚠️ The output is trivially thin
- Criterion scores: InputMin=3, OutputClean=2, Atomic=3, Composable=1, Naming=2, Meta=1 → 12

**Average: 13.3/18 — BORDERLINE**

---

## Candidate 3: `moderncart/reset-settings`

**Label:** Reset Settings to Defaults
**Description:** Resets one or more Modern Cart setting groups back to their factory defaults. Irreversible unless settings are saved first with get-settings.
**Category:** Configuration (write/destructive)
**Input:** groups[] (optional — which groups to reset; defaults to all 4)
**Output:** reset_groups[], was_reset_count
**Permission:** manage_options
**is_destructive:** true (gets dry_run + nonce injected by Abstract_Ability)
**Annotations:** readonly=false, idempotent=true (resetting defaults twice is safe), destructive=true

### Expert 1 — UX/AI (15/18)
- ✅ Clear trigger: "Reset Modern Cart to defaults", "Undo all my settings"
- ✅ Input is simple optional array, no ambiguity
- ✅ Output clearly states what was reset
- ✅ dry_run support (inherited) lets AI preview without committing
- Criterion scores: Trigger=3, Construction=3, Output=3, Labels=3, Scope=2, Safety=1 (destructive but has dry_run) → 15

### Expert 2 — WordPress (15/18)
- ✅ manage_options correct
- ✅ Inherits nonce verification (is_destructive=true)
- ✅ Inherits dry_run support — safe to preview
- ✅ Implementation: delete_option() per group, or update_option() with defaults
- Criterion scores: Capability=3, Standards=3, Edge=2, Sanitize=3, Performance=2, N+1=2 → 15

### Expert 3 — API Design (14/18)
- ✅ Clean, unambiguous input (array of group names)
- ✅ Output clearly states which groups were reset
- ✅ Composable: pair with get-settings (backup) then reset-settings then update-settings (restore)
- ✅ Naming is imperative: reset-settings
- Criterion scores: InputMin=3, OutputClean=3, Atomic=2, Composable=3, Naming=2, Meta=1 → 14

**Average: 14.7/18 — STRONG ✅**

---

## Summary Table

| Ability | Score | Decision |
|---------|-------|----------|
| `moderncart/get-plugin-status` | 16.3 | ✅ Strong |
| `moderncart/reset-settings` | 14.7 | ✅ Strong |
| `moderncart/complete-onboarding` | 13.3 | ⚠️ Borderline |
